<?php

namespace App\Command;

use App\Entity\WeatherData;
use App\Entity\ImportLog;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\HttpClient\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


#[AsCommand(
    name: 'app:fetch-weather-data',
    description: 'Fetches weather data',
    hidden: false,
    aliases: ['app:weather-data']
)]

class FetchWeatherDataCommand extends Command
{
    private $entityManager;
    private $httpClient;
    private $logger;
    private $params;


    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient, LoggerInterface $logger, ParameterBagInterface $params)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->params = $params;
    }

    protected function configure()
    {
        $this->setDescription('Retrieve and store latest weather data from DMI.')
            ->setHelp('This command retrieves the latest weather data from DMI and stores it in the database.')
            ->addArgument('stationId', InputArgument::OPTIONAL, 'StationId for which to retrieve data', '06186')
            ->addArgument('limit', InputArgument::OPTIONAL, 'Number of records to retrieve')
            ->addArgument('offset', InputArgument::OPTIONAL, 'Offset for pagination');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $apiKey = $this->params->get('gravitee_api_key');
            $stationId = $input->getArgument('stationId');
            $limit = $input->getArgument('limit');
            $offset = $input->getArgument('offset');

            $this->logger->info('Fetching weather data started.', ['stationId' => $stationId, 'limit' => $limit, 'offset' => $offset]);

            $dataArray = $this->fetchDataFromApi($apiKey, $stationId, $limit, $offset);
            $this->processData($dataArray);

            $output->writeln('Weather data fetched and stored successfully.');
            $this->logger->info('Weather data fetched and stored successfully.');

            $this->saveImportLog();
            return Command::SUCCESS;
        } catch (ClientException $e) {
            $this->logger->error('Error fetching weather data: ' . $e->getMessage());
            $output->writeln('<error>Error fetching weather data. Please try again later.</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->logger->error('An unexpected error occurred: ' . $e->getMessage());
            $output->writeln('<error>An unexpected error occurred. Please try again later.</error>');
            return Command::FAILURE;
        }
    }


    private function fetchDataFromApi(string $apiKey, string $stationId, ?int $limit, ?int $offset): array
    {
        $baseUrl = "https://dmigw.govcloud.dk/v2/metObs/collections/observation/items";
        $queryParams = ['stationId' => $stationId];

        if ($limit !== null) {
            $queryParams['limit'] = $limit;
        }

        if ($offset !== null) {
            $queryParams['offset'] = $offset;
        }

        $url = $baseUrl . '?' . http_build_query($queryParams);

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'X-Gravitee-Api-Key' => $apiKey,
            ],
        ]);

        return $response->toArray();
    }

    private function processData(array $dataArray): void
    {
        $batchSize = 100;
        $batchCount = 0;

        foreach ($dataArray['features'] as $feature) {
            $parameterId = $feature['properties']['parameterId'];
            $value = $feature['properties']['value'];
            $observedAt = new \DateTime($feature['properties']['observed']);

            // check if we have data for that specific time in our database
            $existingData = $this->entityManager->getRepository(WeatherData::class)->findOneBy([
                'observedAt' => $observedAt,
                'parameterId' => $parameterId,
            ]);

            if (!$existingData) {
                // insert new data
                $weatherData = new WeatherData();
                $weatherData->setObservedAt($observedAt);
                $weatherData->setParameterId($parameterId);

                switch ($parameterId) {
                    case 'temp_dry':
                    case 'temp_dew':
                    case 'humidity':
                    case 'weather':
                        $weatherData->setValue($value);
                        $this->entityManager->persist($weatherData);
                        $batchCount++;

                        if ($batchCount % $batchSize === 0) {
                            $this->entityManager->flush();
                            $this->entityManager->clear();
                        }
                        break;
                }
            }
        }

        $this->entityManager->flush();
    }

    private function saveImportLog(): void
    {
        $importLog = new ImportLog();
        $importLog->setCreatedAt(new \DateTime());

        $this->entityManager->persist($importLog);
        $this->entityManager->flush();
    }
}

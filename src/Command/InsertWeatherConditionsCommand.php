<?php

namespace App\Command;

use App\Entity\WeatherCondition;
use Symfony\Component\Console\Attribute\AsCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'app:insert-weather-condition',
    description: 'Inserts weather conditions into the database',
    hidden: false,
    aliases: ['app:weather-condition']
)]
class InsertWeatherConditionsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('Inserts weather conditions into the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $weatherConditionsData = $this->fetchWeatherConditions();
            $this->insertWeatherConditions($weatherConditionsData);
            $output->writeln('Weather conditions inserted successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function fetchWeatherConditions(): array
    {
        // i have stored the values for each weather code into a json file
        $rootDirectory = dirname(__DIR__, 2);
        $filePath = $rootDirectory . "/weatherCondition.json";

        if (!file_exists($filePath)) {
            throw new \Exception('File not found.');
        }
        // fetch data from json file 
        $weatherConditionsJson = file_get_contents($filePath);
        $weatherConditionsData = json_decode($weatherConditionsJson, true);

        if ($weatherConditionsData === null) {
            throw new \Exception('Failed to decode JSON data.');
        }

        return $weatherConditionsData;
    }

    private function insertWeatherConditions(array $weatherConditionsData): void
    {
        // insert into DB 
        foreach ($weatherConditionsData as $data) {
            $weatherCondition = new WeatherCondition();
            $weatherCondition->setCode($data['code']);
            $weatherCondition->setDescription($data['description']);
            $this->entityManager->persist($weatherCondition);
        }

        $this->entityManager->flush();
    }
}

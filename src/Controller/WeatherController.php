<?php

namespace App\Controller;

use App\Entity\ImportLog;
use App\Repository\WeatherDataRepository;
use App\Repository\WeatherConditionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class WeatherController extends AbstractController
{
    #[Route('/weather}', name: 'weather')]

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function index(WeatherDataRepository $weatherDataRepository, WeatherConditionRepository $weatherConditionRepository): Response
    {
        // fetch weather data
        $weatherData = $weatherDataRepository->findLatest(10);
        $lastImportTime = $this->getLastImportTime();

        $weatherConditions = [];
        // getting weather condition for weather parameter from weather_condition table
        // sometimes there is no weather data for specific stationid
        foreach ($weatherData as $data) {
            if ($data->getParameterId() === 'weather') {
                $weatherConditions[$data->getObservedAt()->format('Y-m-d H:i:s')] = $weatherConditionRepository->findByCode($data->getValue());
            }
        }

        return $this->render('weather/index.html.twig', [
            'lastImportTime' => $lastImportTime,
            'weatherData' => $weatherData,
            'weatherConditions' => $weatherConditions,
        ]);
    }

    private function getLastImportTime(): \DateTime
    {
        // Fetch the last import log entry
        $lastImportLog = $this->entityManager
            ->getRepository(ImportLog::class)
            ->findOneBy([], ['createdAt' => 'DESC']);

        // Check if there's any log entry
        if ($lastImportLog) {
            return $lastImportLog->getCreatedAt();
        }

        // Return a default date if no log entry found
        return new \DateTime('1970-01-01');
    }
}

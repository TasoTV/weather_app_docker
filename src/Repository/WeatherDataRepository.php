<?php

namespace App\Repository;

use App\Entity\WeatherData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\NoResultException;

/**
 * @extends ServiceEntityRepository<WeatherData>
 */
class WeatherDataRepository extends ServiceEntityRepository
{
    private $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, WeatherData::class);
        $this->logger = $logger;
    }

    public function findLatest(int $limit): ?array
    {
        try {
            return $this->createQueryBuilder('w')
                ->orderBy('w.observedAt', 'DESC')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        } catch (NoResultException $e) {
            $this->logger->error('No weather data found: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            $this->logger->error('Error fetching weather data: ' . $e->getMessage());
            return null;
        }
    }
}

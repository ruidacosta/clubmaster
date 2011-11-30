<?php

namespace Club\BookingBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BookingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BookingRepository extends EntityRepository
{
  public function getAllByLocationDate(\Club\UserBundle\Entity\Location $location, \DateTime $date)
  {
    return $this->_em->createQueryBuilder()
      ->select('b')
      ->from('ClubBookingBundle:Booking', 'b')
      ->leftJoin('b.interval' ,'i')
      ->leftJoin('i.field', 'f')
      ->leftJoin('f.location', 'l')
      ->where('l.id = :location')
      ->andWhere('b.date = :date')
      ->setParameter('location', $location->getId())
      ->setParameter('date', $date->format('Y-m-d'))
      ->getQuery()
      ->getResult();
  }
}

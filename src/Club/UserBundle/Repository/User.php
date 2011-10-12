<?php

namespace Club\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * User
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class User extends EntityRepository
{
  public function findNextMemberNumber()
  {
    $dql = "SELECT u FROM ClubUserBundle:User u ORDER BY u.member_number DESC";

    $query = $this->_em->createQuery($dql);
    $query->setMaxResults(1);

    $r = $query->getResult();

    if (!count($r)) return 1;
    return $r[0]->getMemberNumber()+1;
  }

  public function getUsersListWithPagination($filter, $order_by = array(), $offset = 0, $limit = 0) {
    //Create query builder for languages table
    $qb = $this->getQueryBuilderByFilter($filter);

    //Show all if offset and limit not set, also show all when limit is 0
    if ((isset($offset)) && (isset($limit))) {
      if ($limit > 0) {
        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);
      }
      //else we want to display all items on one page
    }
    //Adding defined sorting parameters from variable into query
    foreach ($order_by as $key => $value) {
      $qb->add('orderBy', 'u.' . $key . ' ' . $value);
    }
    //Get our query
    $q = $qb->getQuery();
    //Return result
    return $q->getResult();
  }

  public function getUsersCount($filter) {
    return count($this->getUsers($filter));
  }

  public function getUsers($filter) {
    $qb = $this->getQueryBuilderByFilter($filter);
    return $qb->getQuery()->getResult();
  }

  protected function getQueryBuilderByFilter(\Club\UserBundle\Entity\Filter $filter)
  {
    $qb = $this->getQueryBuilder();

    foreach ($filter->getAttributes() as $attr) {
      if ($attr->getValue() != '') {
        switch ($attr->getAttribute()->getAttributeName()) {
        case 'name':
          $qb = $this->filterName($qb,$attr->getValue());
          break;
        case 'member_number':
          $qb = $this->filterMemberNumber($qb,$attr->getValue());
          break;
        case 'min_age':
          $qb = $this->filterMinAge($qb,$attr->getValue());
          break;
        case 'max_age':
          $qb = $this->filterMaxAge($qb,$attr->getValue());
          break;
        case 'gender':
          $qb = $this->filterGender($qb,$attr->getValue());
          break;
        case 'postal_code':
          $qb = $this->filterPostalCode($qb,$attr->getValue());
          break;
        case 'city':
          $qb = $this->filterCity($qb,$attr->getValue());
          break;
        case 'country':
          $qb = $this->filterCountry($qb,$attr->getValue());
          break;
        case 'active':
          $qb = $this->filterActive($qb,$attr->getValue());
          break;
        case 'has_ticket':
          $qb = $this->filterHasTicket($qb,$attr->getValue());
          break;
        case 'has_subscription':
          $qb = $this->filterHasSubscription($qb,$attr->getValue());
          break;
        case 'subscription_start':
          $qb = $this->filterSubscriptionStart($qb,$attr->getValue());
          break;
        case 'location':
          $qb = $this->filterLocation($qb,explode(",", $attr->getValue()));
          break;
        }
      }
    }

    return $qb;
  }

  protected function getQueryByFilter(\Club\UserBundle\Entity\Filter $filter)
  {
    return $this->getQueryBuilderByFilter($filter)->getQuery();
  }

  public function getByGroup(\Club\UserBundle\Entity\Group $group)
  {
    return $this->getQueryByGroup($group)->getResult();
  }

  public function getQueryByGroup(\Club\UserBundle\Entity\Group $group)
  {
    $qb = $this->getQueryBuilder();

    if ($group->getGender() != '') {
      $qb = $this->filterGender($qb,$group->getGender());
    }

    if ($group->getMinAge() != '') {
      $qb = $this->filterMinAge($qb,$group->getMinAge());
    }

    if ($group->getMaxAge() != '') {
      $qb = $this->filterMaxAge($qb,$group->getMaxAge());
    }

    if ($group->getActiveMember() != '') {
      $qb = $this->filterActive($qb,$group->getActiveMember());
    }

    if (count($group->getLocation()) > 0) {
      $location_arr = array();
      foreach ($group->getLocation() as $location) {
        $location_arr[] = $location->getId();
      }
      $qb = $this->filterLocation($qb,$location_arr);
    }

    return $qb->getQuery();
  }

  protected function getQueryBuilder()
  {
    $this->has_joined_addr = false;
    $this->has_joined_sub = false;

    return $this->_em->createQueryBuilder()
      ->select('u')
      ->from('ClubUserBundle:User','u')
      ->leftJoin('u.profile','p');
  }

  protected function filterName($qb,$value)
  {
    $qb->andWhere("CONCAT(CONCAT(p.first_name, ' '), p.last_name) LIKE :name");
    $qb->setParameter('name', '%'.$value.'%');

    return $qb;
  }

  protected function filterMemberNumber($qb,$value)
  {
    $qb->andWhere(
      $qb->expr()->eq('u.member_number',':number')
    );
    $qb->setParameter('number', $value);

    return $qb;
  }

  protected function filterMinAge($qb,$value)
  {
    $qb->andWhere(
      $qb->expr()->lte('p.day_of_birth',':min_age')
    );
    $qb->setParameter('min_age', date('Y-m-d',mktime(0,0,0,date('n'),date('j'),date('Y')-$value)));

    return $qb;
  }

  protected function filterMaxAge($qb,$value)
  {
    $qb->andWhere(
      $qb->expr()->gte('p.day_of_birth',':max_age')
    );
    $qb->setParameter('max_age', date('Y-m-d',mktime(0,0,0,date('n'),date('j'),date('Y')-$value)));

    return $qb;
  }

  protected function filterGender($qb,$value)
  {
    $qb->andWhere(
      $qb->expr()->eq('p.gender',':gender')
    );
    $qb->setParameter('gender', $value);

    return $qb;
  }

  protected function filterPostalCode($qb,$value)
  {
    if (!$this->has_joined_addr) {
      $qb->join('p.profile_address','pa');
      $this->has_joined_addr = true;
    }

    $qb->andWhere(
      $qb->expr()->eq('pa.postal_code',':postal_code')
    );
    $qb->setParameter('postal_code', $value);

    return $qb;
  }

  protected function filterCity($qb,$value)
  {
    if (!$this->has_joined_addr) {
      $qb->join('p.profile_address','pa');
      $this->has_joined_addr = true;
    }

    $qb->andWhere(
      $qb->expr()->eq('pa.city',':city')
    );
    $qb->setParameter('city', $value);

    return $qb;
  }

  protected function filterCountry($qb,$value)
  {
    if (!$this->has_joined_addr) {
      $qb->join('p.profile_address','pa');
      $this->has_joined_addr = true;
    }

    $qb->andWhere(
      $qb->expr()->eq('pa.country',':country')
    );
    $qb->setParameter('country', $value);

    return $qb;
  }

  protected function filterActive($qb,$value)
  {
    if (!$this->has_joined_sub) {
      $qb->leftJoin('u.subscriptions','s');
      $this->has_joined_sub = true;
    }

    if ($value) {
      $qb->andWhere('((s.start_date <= :date AND s.expire_date >= :date) OR (s.start_date IS NOT NULL AND s.expire_date IS NULL))');
    }
    $qb->setParameter('date',date('Y-m-d H:i:s'));

    return $qb;
  }

  protected function filterHasTicket($qb,$value)
  {
    if (!$this->has_joined_sub) {
      $qb->leftJoin('u.subscriptions','s');
      $this->has_joined_sub = true;
    }

    if ($value) {
      $qb->andWhere('s.type = :type');
    } else {
      $qb->andWhere('s.type <> :type');
    }
    $qb->setParameter('type','ticket');

    return $qb;
  }

  protected function filterHasSubscription($qb,$value)
  {
    if (!$this->has_joined_sub) {
      $qb->leftJoin('u.subscriptions','s');
      $this->has_joined_sub = true;
    }

    if ($value) {
      $qb->andWhere('s.type = :type');
    } else {
      $qb->andWhere('s.type <> :type');
    }
    $qb->setParameter('type','subscription');

    return $qb;
  }

  protected function filterSubscriptionStart($qb,$value)
  {
    $date = unserialize($value);
    if (!$this->has_joined_sub) {
      $qb->leftJoin('u.subscriptions','s');
      $this->has_joined_sub = true;
    }

    if ($value) {
      $qb->andWhere('s.start_date >= :start_date');
    }
    $qb->setParameter('start_date',$date->format('Y-m-d H:i:s'));

    return $qb;
  }

  protected function filterSubscriptionEnd($qb,$value)
  {
    $date = unserialize($value);
    if (!$this->has_joined_sub) {
      $qb->leftJoin('u.subscriptions','s');
      $this->has_joined_sub = true;
    }

    if ($value) {
      $qb->andWhere('s.expire_date <= :end_date');
    }
    $qb->setParameter('end_date',$date->format('Y-m-d H:i:s'));

    return $qb;
  }

  protected function filterLocation($qb,array $value)
  {
    $locations = array();
    foreach ($value as $id) {
      // FIXME, has to be infinitive loop
      $location = $this->_em->find('ClubUserBundle:Location',$id);
      $locations[] = $location->getId();

      if ($location->getLocation()) {
        $locations[] = $location->getLocation()->getId();

        if ($location->getLocation()->getLocation()) {
          $locations[] = $location->getLocation()->getLocation()->getId();

          if ($location->getLocation()->getLocation()->getLocation()) {
            $locations[] = $location->getLocation()->getLocation()->getLocation()->getId();
          }
        }
      }
    }

    $str = "";
    foreach ($locations as $id) {
      $str .= " sl.id = $id OR ";
    }
    $str = preg_replace("/OR $/","",$str);

    if (!$this->has_joined_sub) {
      $qb->leftJoin('u.subscriptions','s');
      $this->has_joined_sub = true;
    }

    $qb
      ->leftJoin('s.location','sl')
      ->andWhere('('.$str.')');

    return $qb;
  }

  public function getGroupsByUser(\Club\UserBundle\Entity\User $user)
  {
    $location_str = '';
    $used = array();

    foreach ($user->getSubscriptions() as $subscription) {
      foreach ($subscription->getLocation() as $location) {

        if (!isset($used[$location->getId()])) {
          $location_str .= 'l.id = '.$location->getId().' OR ';
          $used[$location->getId()] = 1;
        }

        if ($location->getLocation()) {
          if (!isset($used[$location->getLocation()->getId()])) {
            $location_str .= 'l.id = '.$location->getLocation()->getId().' OR ';
            $used[$location->getLocation()->getId()] = 1;
          }

          if ($location->getLocation()->getLocation()) {

            if (!isset($used[$location->getLocation()->getLocation()->getId()])) {
              $location_str .= 'l.id = '.$location->getLocation()->getLocation()->getId().' OR ';
              $used[$location->getLocation()->getLocation()->getId()] = 1;
            }
          }
        }
      }
    }

    return $this->_em->createQueryBuilder()
      ->select('g')
      ->from('ClubUserBundle:Group', 'g')
      ->leftJoin('g.location','l')
      ->where('g.group_type = :type')
      ->andWhere('(g.gender IS NULL OR g.gender=:gender)')
      ->andWhere('(g.min_age IS NULL OR g.min_age <= :min_age)')
      ->andWhere('(g.max_age IS NULL OR g.max_age >= :max_age)')
      ->andWhere('(g.active_member IS NULL OR g.active_member = :active_member)')
      ->andWhere('('.$location_str.' l.id IS NULL)')
      ->setParameter('type', 'dynamic')
      ->setParameter('gender', $user->getProfile()->getGender())
      ->setParameter('min_age', $user->getProfile()->getAge())
      ->setParameter('max_age', $user->getProfile()->getAge())
      ->setParameter('active_member', $user->getMemberStatus())
      ->getQuery()
      ->getResult();
  }
}

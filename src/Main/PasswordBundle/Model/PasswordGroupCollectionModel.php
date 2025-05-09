<?php

namespace Main\PasswordBundle\Model;

use Main\PasswordBundle\Entity\PasswordGroup;

class PasswordGroupCollectionModel
{
    /**
     * @var array
     */
    protected $map;

    /**
     * @var PasswordGroup[]
     */
    protected $passwordGroups;

    /**
     * @var array
     */
    protected $passwordGroupRights;

    public function __construct()
    {
        $this->map = [];
        $this->passwordGroups = [];
        $this->passwordGroupRights = [];
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param int           $right
     *
     * @return bool
     */
    public function addPasswordGroup(PasswordGroup $passwordGroup, $right)
    {
        $this->addPasswordGroupToCache($passwordGroup);
        $this->addPasswordGroupRightToCache($passwordGroup, $right);

        $parentIds = [];

        $this->getParentIds($parentIds, $passwordGroup);

        $pointer = &$this->map;

        if (count($parentIds)) {
            $parentIndexes = count($parentIds) - 1;
            for ($i = $parentIndexes; $i >= 0; --$i) {
                if (!array_key_exists($parentIds[$i], $pointer)) {
                    $pointer[$parentIds[$i]] = [];
                }
                $pointer = &$pointer[$parentIds[$i]];
            }
        }

        if (!array_key_exists($passwordGroup->getId(), $pointer)) {
            $pointer[$passwordGroup->getId()] = [];
        }

        return true;
    }

    /**
     * @return array
     */
    public function getCurrent()
    {
        return $this->get($this->map);
    }

    /**
     * @param array         $parentIds
     * @param PasswordGroup $passwordGroup
     */
    protected function getParentIds(array &$parentIds, PasswordGroup $passwordGroup)
    {
        if ($passwordGroup->getParent() instanceof PasswordGroup) {
            $this->addPasswordGroupToCache($passwordGroup->getParent());
            $this->addPasswordGroupRightToCache($passwordGroup->getParent(), 0);
            $parentIds[] = $passwordGroup->getParent()->getId();
            $this->getParentIds($parentIds, $passwordGroup->getParent());
        }
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @return bool
     */
    protected function addPasswordGroupToCache(PasswordGroup $passwordGroup)
    {
        if (array_key_exists($passwordGroup->getId(), $this->passwordGroups)) {
            return false;
        }

        $this->passwordGroups[$passwordGroup->getId()] = $passwordGroup;

        return true;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param int           $right
     */
    protected function addPasswordGroupRightToCache(PasswordGroup $passwordGroup, $right)
    {
        if (!array_key_exists($passwordGroup->getId(), $this->passwordGroupRights)) {
            $this->passwordGroupRights[$passwordGroup->getId()] = $right;
        } else {
            if ($right > $this->passwordGroupRights[$passwordGroup->getId()]) {
                $this->passwordGroupRights[$passwordGroup->getId()] = $right;
            }
        }
    }

    /**
     * @param array $map
     *
     * @return array
     */
    protected function get(array $map)
    {
        $result = [];

        foreach ($map as $groupId => $childrenIds) {
            $result[] = [
                'id' => $this->passwordGroups[$groupId]->getId(),
                'name' => $this->passwordGroups[$groupId]->getName(),
                'icon' => $this->passwordGroups[$groupId]->getIcon(),
                'sorting' => $this->passwordGroups[$groupId]->getSorting(),
                'right' => $this->passwordGroupRights[$groupId],
                'children' => $this->get($childrenIds),
            ];
        }

        usort($result, function($a, $b) {
            return ($a['sorting'] < $b['sorting']) ? -1 : 1;
        });

        return $result;
    }
}

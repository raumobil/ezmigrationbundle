<?php

namespace Kaliop\eZMigrationBundle\Core\Matcher;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use Kaliop\eZMigrationBundle\API\Collection\UserGroupCollection;
use Kaliop\eZMigrationBundle\API\KeyMatcherInterface;

/**
 * @todo add matching all groups of a user, all child groups of a group
 */
class UserGroupMatcher extends RepositoryMatcher implements KeyMatcherInterface
{
    use FlexibleKeyMatcherTrait;

    const MATCH_USERGROUP_ID = 'usergroup_id';
    const MATCH_CONTENT_REMOTE_ID = 'content_remote_id';

    protected $allowedConditions = array(
        self::MATCH_USERGROUP_ID,
        self::MATCH_CONTENT_REMOTE_ID,
        // aliases
        'id'
    );
    protected $returns = 'UserGroup';

    /**
     * @param array $conditions key: condition, value: int / string / int[] / string[]
     * @return UserGroupCollection
     */
    public function match(array $conditions)
    {
        return $this->matchUserGroup($conditions);
    }

    /**
     * @param array $conditions key: condition, value: int / string / int[] / string[]
     * @return UserGroupCollection
     */
    public function matchUserGroup(array $conditions)
    {
        $this->validateConditions($conditions);

        foreach ($conditions as $key => $values) {

            if (!is_array($values)) {
                $values = array($values);
            }

            switch ($key) {
                case 'id':
                case self::MATCH_USERGROUP_ID:
                    return new UserGroupCollection($this->findUserGroupsById($values));
                case self::MATCH_CONTENT_REMOTE_ID:
                    return new UserGroupCollection($this->findUserGroupsByContentRemoteIds($values));

            }
        }
    }

    /**
     * When matching by key, we accept user group Id and it's remote Id only
     * @param int|string $key
     * @return array
     */
    protected function getConditionsFromKey($key)
    {
        if (is_int($key) || ctype_digit($key)) {
            return array(self::MATCH_USERGROUP_ID => $key);
        }
        return array(self::MATCH_CONTENT_REMOTE_ID => $key);
    }

    /**
     * @param int[] $userGroupIds
     * @return UserGroup[]
     */
    protected function findUserGroupsById(array $userGroupIds)
    {
        $userGroups = [];

        foreach ($userGroupIds as $userGroupId) {
            // return unique contents
            $userGroup = $this->repository->getUserService()->loadUserGroup($userGroupId);

            $userGroups[$userGroup->id] = $userGroup;
        }

        return $userGroups;
    }

    /**
     * @param string[] $remoteContentIds
     * @return UserGroup[]
     */
    protected function findUserGroupsByContentRemoteIds(array $remoteContentIds)
    {
        $userGroups = [];

        foreach ($remoteContentIds as $remoteContentId) {
            // return unique contents
            $content = $this->repository->getContentService()->loadContentByRemoteId($remoteContentId);
            $userGroup = $this->repository->getUserService()->loadUserGroup($content->id);

            $userGroups[$userGroup->id] = $userGroup;
        }

        return $userGroups;
    }
}

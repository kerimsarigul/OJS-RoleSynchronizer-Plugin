<?php
/**
 * @file plugins/generic/roleSynchronizer/RoleSynchronizerHandler.inc.php
 *
 * OJS 3.3 Role Synchronizer Handler - Final Working Version with security fixes
 */

import('classes.handler.Handler');

class RoleSynchronizerHandler extends Handler {

    /**
     * Synchronize roles
     */
    public function synchronize($args, $request) {
        // CSRF token kontrolü ekle
        if (!$request->checkCSRF()) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'CSRF Token Validation Failed.'));
            return;
        }

        $plugin = PluginRegistry::getPlugin('generic', 'rolesynchronizerplugin');
        $context = $request->getContext();
        
        if (!$context) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'No context found'));
            return;
        }
        
        $sourceJournalId = isset($_POST['sourceJournalId']) ? (int)$_POST['sourceJournalId'] : 0;
        $targetJournalId = $context->getId();
        $selectedRoles = isset($_POST['selectedRoles']) ? $_POST['selectedRoles'] : array();
        
        if (!$sourceJournalId) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'No source journal selected'));
            return;
        }
        
        if (empty($selectedRoles)) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'No roles selected'));
            return;
        }
        
        try {
            $result = $this->performRoleSynchronization($sourceJournalId, $targetJournalId, $selectedRoles);
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            error_log('Role Sync Error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
    }
    
    /**
     * Perform role synchronization using user_user_groups table
     */
    private function performRoleSynchronization($sourceJournalId, $targetJournalId, $selectedRoles = array()) {
        $syncedUsers = 0;
        
        try {
            $userGroupDao = DAORegistry::getDAO('UserGroupDAO');
            $userGroupAssignmentDao = DAORegistry::getDAO('UserGroupAssignmentDAO');
            
            // Convert selectedRoles to integers for security
            $selectedRoleIds = array_map('intval', $selectedRoles);
            
            if (empty($selectedRoleIds)) {
                throw new Exception("No roles selected for synchronization");
            }
            
            // Güvenli SQL sorgusu için soru işareti placeholders oluşturma
            $rolePlaceholders = implode(',', array_fill(0, count($selectedRoleIds), '?'));
            
            // Get all user-role combinations from source journal for selected roles only
            $sourceQuery = 'SELECT DISTINCT uug.user_id, ug.role_id, ug.user_group_id
                           FROM user_user_groups uug 
                           INNER JOIN user_groups ug ON uug.user_group_id = ug.user_group_id 
                           WHERE ug.context_id = ? AND ug.role_id IN (' . $rolePlaceholders . ')';
            
            $binds = array_merge([$sourceJournalId], $selectedRoleIds);
            
            $sourceResult = $userGroupAssignmentDao->retrieve($sourceQuery, $binds);
            $sourceAssignments = array();
            
            while ($row = $sourceResult->current()) {
                $sourceAssignments[] = array(
                    'user_id' => $row->user_id,
                    'role_id' => $row->role_id,
                    'user_group_id' => $row->user_group_id
                );
                $sourceResult->next();
            }
            
            // Get target journal user groups indexed by role ID (only for selected roles)
            $targetUserGroups = $userGroupDao->getByContextId($targetJournalId);
            $targetGroupsByRoleId = array();

            while ($targetGroup = $targetUserGroups->next()) {
                $roleId = $targetGroup->getRoleId();
                // Only include selected roles
                if (in_array($roleId, $selectedRoleIds)) {
                    if (!isset($targetGroupsByRoleId[$roleId])) {
                        $targetGroupsByRoleId[$roleId] = array();
                    }
                    $targetGroupsByRoleId[$roleId][] = $targetGroup;
                }
            }
            
            // Process each source assignment
            foreach ($sourceAssignments as $assignment) {
                $userId = $assignment['user_id'];
                $roleId = $assignment['role_id'];
                
                // Check if target journal has groups with the same role
                if (!isset($targetGroupsByRoleId[$roleId])) {
                    continue;
                }
                
                // Check if user already has this role in target journal
                $existingQuery = 'SELECT COUNT(*) as count 
                                 FROM user_user_groups uug 
                                 INNER JOIN user_groups ug ON uug.user_group_id = ug.user_group_id 
                                 WHERE uug.user_id = ? AND ug.context_id = ? AND ug.role_id = ?';
                
                $existingResult = $userGroupAssignmentDao->retrieve($existingQuery, array((int)$userId, (int)$targetJournalId, (int)$roleId));
                $existingRow = $existingResult->current();
                $hasRole = ($existingRow && $existingRow->count > 0);
                
                if (!$hasRole) {
                    // Get target group for this role
                    $targetGroup = $targetGroupsByRoleId[$roleId][0];
                    
                    // Insert new assignment
                    try {
                        $insertQuery = 'INSERT INTO user_user_groups (user_group_id, user_id) VALUES (?, ?)';
                        $userGroupAssignmentDao->update($insertQuery, array((int)$targetGroup->getId(), (int)$userId));
                        $syncedUsers++;
                    } catch (Exception $e) {
                        // Skip if already exists (duplicate key error)
                        if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                            error_log('Insert error for user ' . $userId . ': ' . $e->getMessage());
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            throw new Exception("Synchronization error: " . $e->getMessage());
        }

        $roleNames = array();
        foreach ($selectedRoleIds as $roleId) {
            $roleNames[] = $this->getRoleName($roleId);
        }
        $rolesText = implode(', ', $roleNames);

        return array(
            'success' => true,
            'message' => $syncedUsers > 0 
                ? "Successfully synchronized $syncedUsers user(s) for roles: $rolesText." 
                : "No new roles to synchronize for selected roles: $rolesText. All users already have matching roles in the target journal.",
            'syncedUsers' => $syncedUsers
        );
    }
    
    /**
     * Get role name by role ID with fallback
     */
    private function getRoleName($roleId) {
        $roleNames = array(
            ROLE_ID_SITE_ADMIN => 'user.role.siteAdmin',
            ROLE_ID_MANAGER => 'user.role.manager',
            ROLE_ID_SUB_EDITOR => 'user.role.subEditor',
            ROLE_ID_AUTHOR => 'user.role.author',
            ROLE_ID_REVIEWER => 'user.role.reviewer',
            ROLE_ID_ASSISTANT => 'user.role.assistant',
            ROLE_ID_READER => 'user.role.reader',
            ROLE_ID_SUBSCRIPTION_MANAGER => 'user.role.subscriptionManager'
        );

        $roleKey = isset($roleNames[$roleId]) ? $roleNames[$roleId] : null;

        if ($roleKey) {
            $translatedName = __($roleKey);
            // Check if translation failed and returned the key itself
            if (strpos($translatedName, '##') !== false) {
                // Fallback to hardcoded names if translation fails
                return $this->getBackupRoleName($roleId);
            }
            return $translatedName;
        }
        
        return 'Unknown Role (' . $roleId . ')';
    }

    /**
     * Get hardcoded role name for fallback
     */
    private function getBackupRoleName($roleId) {
        $backupNames = array(
            ROLE_ID_SITE_ADMIN => 'Site Admin',
            ROLE_ID_MANAGER => 'Journal Manager',
            ROLE_ID_SUB_EDITOR => 'Section Editor',
            ROLE_ID_AUTHOR => 'Author',
            ROLE_ID_REVIEWER => 'Reviewer',
            ROLE_ID_ASSISTANT => 'Assistant',
            ROLE_ID_READER => 'Reader',
            ROLE_ID_SUBSCRIPTION_MANAGER => 'Subscriber Manager'
        );
        return isset($backupNames[$roleId]) ? $backupNames[$roleId] : 'Unknown Role (' . $roleId . ')';
    }
}
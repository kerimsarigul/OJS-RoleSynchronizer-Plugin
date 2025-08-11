<?php
/**
 * @file plugins/generic/roleSynchronizer/RoleSynchronizerPlugin.php
 *
 * OJS 3.3 Role Synchronizer Plugin with Role Selection
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class RoleSynchronizerPlugin extends GenericPlugin {

    /**
     * @copydoc Plugin::register()
     */
    public function register($category, $path, $mainContextId = null) {
        $success = parent::register($category, $path, $mainContextId);
        if ($success && $this->getEnabled($mainContextId)) {
            $this->addLocaleData();
            HookRegistry::register('LoadHandler', array($this, 'setupHandler'));
        }
        return $success;
    }

    /**
     * Setup request handler
     */
    public function setupHandler($hookName, $params) {
        $page = $params[0];
        $op = $params[1];
        
        if ($page == 'roleSynchronizer') {
            define('HANDLER_CLASS', 'RoleSynchronizerHandler');
            $this->import('RoleSynchronizerHandler');
            return true;
        }
        return false;
    }

    /**
     * Get the plugin display name
     */
    public function getDisplayName() {
        return __('plugins.generic.roleSynchronizer.displayName');
    }

    /**
     * Get the plugin description
     */
    public function getDescription() {
        return __('plugins.generic.roleSynchronizer.description');
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $actionArgs) {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }

        $router = $request->getRouter();
        import('lib.pkp.classes.linkAction.request.AjaxModal');
        $actions[] = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url($request, null, null, 'manage', null, array(
                    'verb' => 'settings',
                    'plugin' => $this->getName(),
                    'category' => 'generic'
                )),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        );

        return $actions;
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request) {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                return $this->showSettings($request);
            default:
                return parent::manage($args, $request);
        }
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
        
        return __('user.role.unknown') . ' (' . $roleId . ')';
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
            ROLE_ID_SUBSCRIPTION_MANAGER => 'Subscription Manager'
        );
        return isset($backupNames[$roleId]) ? $backupNames[$roleId] : 'Unknown Role (' . $roleId . ')';
    }

    /**
     * Show plugin settings form with role selection
     */
    public function showSettings($request) {
        $context = $request->getContext();
        $currentJournalId = $context->getId();

        // Get all other journals
        $contextDao = Application::getContextDAO();
        $contexts = $contextDao->getAll(true);
        $availableJournals = array();

        while ($journal = $contexts->next()) {
            if ($journal->getId() != $currentJournalId) {
                $availableJournals[] = array(
                    'id' => $journal->getId(),
                    'name' => $journal->getLocalizedName()
                );
            }
        }

        $currentJournalName = $context->getLocalizedName();

        // Get all available roles from current journal
        $userGroupDao = DAORegistry::getDAO('UserGroupDAO');
        $currentJournalGroups = $userGroupDao->getByContextId($currentJournalId);
        $availableRoles = array();
        
        while ($group = $currentJournalGroups->next()) {
            $roleId = $group->getRoleId();
            if (!isset($availableRoles[$roleId])) {
                $availableRoles[$roleId] = $this->getRoleName($roleId);
            }
        }

        // CSRF Token
        $csrfToken = $request->getSession()->getCSRFToken();
        
        // Plugin URL
        $dispatcher = $request->getDispatcher();
        $ajaxUrl = $dispatcher->url(
            $request,
            ROUTE_PAGE,
            null,
            'roleSynchronizer',
            'synchronize',
            null
        );

        // Build HTML content with role selection
        $html = '
        <div id="roleSynchronizerSettings">
            <h3>' . __('plugins.generic.roleSynchronizer.settings.title') . '</h3>
            <p>' . __('plugins.generic.roleSynchronizer.settings.description') . '</p>
            
            <div class="section">
                <p><strong>' . __('plugins.generic.roleSynchronizer.settings.currentJournal') . ':</strong> ' . htmlspecialchars($currentJournalName) . '</p>
            </div>
            
            <div class="section">
                <label for="sourceJournalSelect">' . __('plugins.generic.roleSynchronizer.settings.selectSource') . ':</label>
                <select id="sourceJournalSelect" class="form-control" style="max-width: 400px;">
                    <option value="">' . __('plugins.generic.roleSynchronizer.settings.chooseJournal') . '</option>';
    
        foreach ($availableJournals as $journal) {
            $html .= '<option value="' . $journal['id'] . '">' . htmlspecialchars($journal['name']) . '</option>';
        }
        
        $html .= '
                </select>
            </div>
            
            <div class="section">
                <label>' . __('plugins.generic.roleSynchronizer.settings.selectRoles') . ':</label>
                <div id="roleSelectionContainer" style="margin-top: 10px;">
                    <div style="margin-bottom: 10px;">
                        <label>
                            <input type="checkbox" id="selectAllRoles" style="margin-right: 5px;">
                            <strong>' . __('plugins.generic.roleSynchronizer.settings.selectAllRoles') . '</strong>
                        </label>
                    </div>';
                    
        foreach ($availableRoles as $roleId => $roleName) {
            $html .= '
                    <div style="margin-bottom: 5px;">
                        <label>
                            <input type="checkbox" class="role-checkbox" value="' . $roleId . '" style="margin-right: 5px;">
                            ' . htmlspecialchars($roleName) . '
                        </label>
                    </div>';
        }
        
        $html .= '
                </div>
            </div>
            
            <div class="section" style="margin-top: 20px;">
                <button id="synchronizeRolesBtn" class="pkp_button submitFormButton" disabled>
                    ' . __('plugins.generic.roleSynchronizer.settings.synchronize') . '
                </button>
            </div>
            
            <div id="syncResult" style="margin-top: 20px; display: none;"></div>
            
            <script>
            $(document).ready(function() {
                var $sourceSelect = $("#sourceJournalSelect");
                var $button = $("#synchronizeRolesBtn");
                var $result = $("#syncResult");
                var $selectAllRoles = $("#selectAllRoles");
                var $roleCheckboxes = $(".role-checkbox");
                
                // Handle source journal selection
                $sourceSelect.on("change", function() {
                    checkFormValidity();
                });
                
                // Handle select all roles
                $selectAllRoles.on("change", function() {
                    var isChecked = $(this).is(":checked");
                    $roleCheckboxes.prop("checked", isChecked);
                    checkFormValidity();
                });
                
                // Handle individual role checkboxes
                $roleCheckboxes.on("change", function() {
                    var totalRoles = $roleCheckboxes.length;
                    var checkedRoles = $roleCheckboxes.filter(":checked").length;
                    
                    if (checkedRoles === totalRoles) {
                        $selectAllRoles.prop("checked", true);
                    } else {
                        $selectAllRoles.prop("checked", false);
                    }
                    
                    checkFormValidity();
                });
                
                // Check if form is valid to enable/disable button
                function checkFormValidity() {
                    var sourceSelected = $sourceSelect.val();
                    var rolesSelected = $roleCheckboxes.filter(":checked").length > 0;
                    
                    if (sourceSelected && rolesSelected) {
                        $button.prop("disabled", false);
                    } else {
                        $button.prop("disabled", true);
                    }
                }
                
                // Handle synchronize button click
                $button.on("click", function() {
                    var sourceId = $sourceSelect.val();
                    var selectedRoles = [];
                    
                    $roleCheckboxes.filter(":checked").each(function() {
                        selectedRoles.push($(this).val());
                    });
                    
                    if (!sourceId) {
                        alert("' . __('plugins.generic.roleSynchronizer.error.noSource') . '");
                        return;
                    }
                    
                    if (selectedRoles.length === 0) {
                        alert("' . __('plugins.generic.roleSynchronizer.error.noRoles') . '");
                        return;
                    }
                    
                    $button.prop("disabled", true);
                    $result.hide().removeClass("alert-success alert-danger");
                    
                    $.ajax({
                        url: "' . $ajaxUrl . '",
                        type: "POST",
                        data: {
                            sourceJournalId: sourceId,
                            selectedRoles: selectedRoles,
                            csrfToken: "' . $csrfToken . '"
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.success) {
                                $result.html("<div class=\"alert alert-success\">" + response.message + "</div>").show();
                            } else {
                                $result.html("<div class=\"alert alert-danger\">" + response.message + "</div>").show();
                            }
                            $button.prop("disabled", false);
                        },
                        error: function(xhr) {
                            var errorMsg = "' . __('plugins.generic.roleSynchronizer.error.connection') . '";
                            try {
                                var res = JSON.parse(xhr.responseText);
                                if (res.message) errorMsg = res.message;
                            } catch (e) {}
                            $result.html("<div class=\"alert alert-danger\">" + errorMsg + "</div>").show();
                            $button.prop("disabled", false);
                        }
                    });
                });
                
                // Initially check form validity
                checkFormValidity();
            });
            </script>
            
            <style>
            #roleSynchronizerSettings .section {
                margin-bottom: 15px;
            }
            #roleSelectionContainer {
                border: 1px solid #ddd;
                padding: 15px;
                border-radius: 4px;
                background-color: #f9f9f9;
                max-height: 200px;
                overflow-y: auto;
            }
            .alert {
                padding: 10px;
                border-radius: 4px;
            }
            .alert-success {
                background-color: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
            }
            .alert-danger {
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
            }
            </style>
        </div>';

        import('lib.pkp.classes.core.JSONMessage');
        return new JSONMessage(true, $html);
    }

    /**
     * @copydoc Plugin::getInstallMigration()
     */
    public function getInstallMigration() {
        return null;
    }
}
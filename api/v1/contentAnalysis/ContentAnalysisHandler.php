<?php

namespace APP\plugins\generic\contentAnalysis\API\v1\contentAnalysis;

use APP\core\Application;
use PKP\handler\APIHandler;
use PKP\security\Role;
use APP\facades\Repo;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\authorization\SubmissionAccessPolicy;
use PKP\security\authorization\UserRolesRequiredPolicy;

class ContentAnalysisHandler extends APIHandler
{
    public function __construct()
    {
        $this->_handlerPath = 'contentAnalysis';
        $roles = [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_AUTHOR];
        $this->_endpoints = [
            'PUT' => [
                [
                    'pattern' => $this->getEndpointPattern() . '/saveForm/{submissionId:\d+}',
                    'handler' => [$this, 'saveForm'],
                    'roles' => $roles,
                ],
            ],
        ];

        parent::__construct();
    }

    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new UserRolesRequiredPolicy($request), true);
        $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        $this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));

        return parent::authorize($request, $args, $roleAssignments);
    }

    public function saveForm($slimRequest, $response, $args)
    {
        $params = $slimRequest->getParsedBody() ?? [];
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);

        $ethicsCouncil = $params['ethicsCouncil'] ?? null;
        if (!$this->isValidSelection($ethicsCouncil, true)) {
            return $response->withJson(['error' => 'Invalid ethicsCouncil value'], 400);
        }

        $editFields = ['researchInvolvingHumansOrAnimals' => is_null($ethicsCouncil) ? null : (string) $ethicsCouncil];

        if (isset($params['documentType'])) {
            if (!$this->isValidSelection($params['documentType'], false)) {
                return $response->withJson(['error' => 'Invalid documentType value'], 400);
            }
            $editFields['nonArticle'] = (string) $params['documentType'];
        }

        Repo::submission()->edit($submission, $editFields);

        return $response->withStatus(200);
    }

    private function isValidSelection($value, bool $allowNull): bool
    {
        if (is_null($value)) {
            return $allowNull;
        }

        return (is_string($value) || is_int($value)) && in_array((string) $value, ['0', '1'], true);
    }
}

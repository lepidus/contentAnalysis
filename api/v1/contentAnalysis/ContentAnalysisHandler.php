<?php

namespace APP\plugins\generic\contentAnalysis\API\v1\contentAnalysis;

use PKP\handler\APIHandler;
use PKP\security\Role;
use APP\facades\Repo;
use PKP\security\authorization\ContextAccessPolicy;
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

        return parent::authorize($request, $args, $roleAssignments);
    }

    public function saveForm($slimRequest, $response, $args)
    {
        $params = $slimRequest->getParsedBody();
        $submission = Repo::submission()->get($args['submissionId']);

        $editFields = ['researchInvolvingHumansOrAnimals' => $params['ethicsCouncil']];

        if (isset($params['documentType'])) {
            $editFields['nonArticle'] = $params['documentType'];
        }

        Repo::submission()->edit($submission, $editFields);

        return $response->withStatus(200);
    }
}

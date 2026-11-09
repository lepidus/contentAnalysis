<?php

namespace APP\plugins\generic\contentAnalysis\classes\api\v1;

use APP\core\Application;
use APP\facades\Repo;
use APP\plugins\generic\contentAnalysis\classes\DocumentChecklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use PKP\core\PKPBaseController;
use PKP\core\PKPRequest;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\authorization\SubmissionAccessPolicy;
use PKP\security\authorization\UserRolesRequiredPolicy;
use PKP\security\Role;

class ContentAnalysisController extends PKPBaseController
{
    public function getHandlerPath(): string
    {
        return 'contentAnalysis';
    }

    public function getRouteGroupMiddleware(): array
    {
        return [
            'has.user',
            'has.context',
            self::roleAuthorizer([
                Role::ROLE_ID_MANAGER,
                Role::ROLE_ID_SUB_EDITOR,
                Role::ROLE_ID_AUTHOR,
            ]),
        ];
    }

    public function authorize(PKPRequest $request, array &$args, array $roleAssignments): bool
    {
        $this->addPolicy(new UserRolesRequiredPolicy($request), true);
        $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        $this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));

        return parent::authorize($request, $args, $roleAssignments);
    }

    public function getGroupRoutes(): void
    {
        Route::put('saveForm/{submissionId}', $this->saveForm(...))
            ->name('contentAnalysis.saveForm')
            ->whereNumber('submissionId');

        Route::get('checklist/{submissionId}', $this->getChecklist(...))
            ->name('contentAnalysis.checklist')
            ->whereNumber('submissionId');
    }

    public function saveForm(IlluminateRequest $request): JsonResponse
    {
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);

        $ethicsCouncil = $request->input('ethicsCouncil');
        if ($ethicsCouncil === null || !in_array((string) $ethicsCouncil, ['0', '1'], true)) {
            return response()->json(
                ['error' => 'Invalid ethicsCouncil value'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $editFields = ['researchInvolvingHumansOrAnimals' => (string) $ethicsCouncil];

        $documentType = $request->input('documentType');
        if ($documentType !== null) {
            if (!in_array((string) $documentType, ['0', '1'], true)) {
                return response()->json(
                    ['error' => 'Invalid documentType value'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $editFields['nonArticle'] = (string) $documentType;
        }

        Repo::submission()->edit($submission, $editFields);

        return response()->json([], Response::HTTP_OK);
    }

    public function getChecklist(IlluminateRequest $request): JsonResponse
    {
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);

        $galleys = Repo::galley()
            ->getCollector()
            ->filterByPublicationIds([$submission->getCurrentPublication()->getId()])
            ->getMany()
            ->toArray();

        if (count($galleys) === 0 || !$galleys[0]->getFile()) {
            return response()->json(
                ['noGalley' => true],
                Response::HTTP_OK
            );
        }

        $galley = $galleys[0];
        $path = \Config::getVar('files', 'files_dir') . DIRECTORY_SEPARATOR . $galley->getFile()->getData('path');

        $checklist = new DocumentChecklist($path);
        $checklistData = $checklist->executeChecklist($submission);

        return response()->json($checklistData, Response::HTTP_OK);
    }
}

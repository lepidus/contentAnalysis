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
        $submissionId = (int) $request->route('submissionId');
        $submission = Repo::submission()->get($submissionId);

        if (!$submission) {
            return response()->json(
                ['error' => 'Submission not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $params = $request->all();
        $editFields = ['researchInvolvingHumansOrAnimals' => $params['ethicsCouncil']];

        if (isset($params['documentType'])) {
            $editFields['nonArticle'] = $params['documentType'];
        }

        Repo::submission()->edit($submission, $editFields);

        return response()->json([], Response::HTTP_OK);
    }

    public function getChecklist(IlluminateRequest $request): JsonResponse
    {
        $submissionId = (int) $request->route('submissionId');
        $submission = Repo::submission()->get($submissionId);

        if (!$submission) {
            return response()->json(
                ['error' => 'Submission not found'],
                Response::HTTP_NOT_FOUND
            );
        }

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

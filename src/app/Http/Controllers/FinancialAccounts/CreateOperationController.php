<?php

namespace App\Http\Controllers\FinancialAccounts;

use App\Http\Requests\FinancialOperations\CreateOperationRequest;
use App\Models\Account;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\throwException;

class CreateOperationController extends GeneralOperationController
{

    /**
     * Handles the request to create a new financial operation.
     *
     * @param CreateOperationRequest $request
     * @return Application|ResponseFactory|Response
     */
    public function handleCreateOperationRequest(CreateOperationRequest $request)
    {
        $account = Account::findOrFail($request->validated('account_id'));

        $attachmentPath = null;
        $file = $request->file('attachment');
        if ($file) $attachmentPath = $this->saveAttachment($account->getUserId(), $file);

        DB::beginTransaction();
        try{
            $operation = $this->createOperation($request, $account, $attachmentPath);
            if ($operation->isLending()) $this->upsertLending($request, $operation->id);
        }
        catch (Exception $e){
            $this->deleteFileIfExists($attachmentPath);
            DB::rollBack();
            // return \response($e->getMessage(), 500); //for debugging purposes
            return response('finance_operations.create.failure', 500);
        }
        DB::commit();
        return response(trans('finance_operations.create.success'), 201);
    }


    /**
     * Creates a new operation DB record using the data from the request. Returns the created operation model.
     *
     * @param $request
     * @param $account
     * @param $attachment
     * @return mixed
     */
    private function createOperation($request, $account, $attachment)
    {
        $operation = $account->financialOperations()->create([
            'account_id' => $account->id,
            'title' => $request->validated('title'),
            'date' => $request->validated('date'),
            'operation_type_id' => $request->validated('operation_type_id'),
            'subject' => $request->validated('subject'),
            'sum' => $request->validated('sum'),
            'attachment' => $attachment,
        ]);
        if (!$operation->exists) throwException(new Exception('The operation wasn\'t created.'));
        return $operation;
    }
}

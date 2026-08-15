<?php

namespace Modules\UserManagement\Service;


use App\Service\BaseService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\AdminModule\Entities\ActivityLog;
use Modules\TransactionManagement\Traits\TransactionTrait;
use Modules\UserManagement\Entities\WithdrawRequest;
use Modules\UserManagement\Repository\WithdrawMethodRepositoryInterface;
use Modules\UserManagement\Repository\WithdrawRequestRepositoryInterface;
use Modules\UserManagement\Service\Interfaces\WithdrawRequestServiceInterface;

class WithdrawRequestService extends BaseService implements WithdrawRequestServiceInterface
{
    use TransactionTrait;

    protected $withdrawRequestRepository;

    public function __construct(WithdrawRequestRepositoryInterface $withdrawRequestRepository)
    {
        parent::__construct($withdrawRequestRepository);
        $this->withdrawRequestRepository = $withdrawRequestRepository;
    }

    public function update(int|string $id, array $data = []): ?Model
    {
        $withdrawRequestData = null;
        $withdrawRequest = $this->withdrawRequestRepository->findOne(id: $id, relations: ['user' => []]);
        if (array_key_exists('rejection_cause', $data) && !is_null($data['rejection_cause'])) {
            $attributes['rejection_cause'] = $data['rejection_cause'];
        }
        if (array_key_exists('approval_note', $data) && !is_null($data['approval_note'])) {
            $attributes['approval_note'] = $data['approval_note'];
        }
        if (array_key_exists('denied_note', $data) && !is_null($data['denied_note'])) {
            $attributes['denied_note'] = $data['denied_note'];
        }

        $adminId = auth()->user()->id;

        if ($data['status'] == 'reverse' && $withdrawRequest->status != PENDING) {
            $attributes['status'] = PENDING;
            $attributes['approved_by'] = null;
            $withdrawRequestData = $this->withdrawRequestRepository->update(id: $id, data: $attributes);
            $this->withdrawRequestReverseTransaction($withdrawRequest->user, $withdrawRequest->amount, $withdrawRequest);
            $this->withdrawRequestNotificationSendDriver(data: $data, withdrawRequestData: $withdrawRequestData);
        }
        if ($withdrawRequest->status == PENDING && $data['status'] == APPROVED) {
            $attributes['status'] = APPROVED;
            $attributes['approved_by'] = $adminId;
            $withdrawRequestData = $this->withdrawRequestRepository->update(id: $id, data: $attributes);
            $this->withdrawRequestNotificationSendDriver(data: $data, withdrawRequestData: $withdrawRequestData);
        }
        if ($withdrawRequest->status == PENDING && $data['status'] == DENIED) {
            $attributes['status'] = DENIED;
            $withdrawRequestData = $this->withdrawRequestRepository->update(id: $id, data: $attributes);
            $this->withdrawRequestCancelTransaction($withdrawRequest->user, $withdrawRequest->amount, $withdrawRequest);
            $this->withdrawRequestNotificationSendDriver(data: $data, withdrawRequestData: $withdrawRequestData);
        }
        if ($withdrawRequest->status == APPROVED && $data['status'] == SETTLED) {
            if ($withdrawRequest->approved_by == $adminId) {
                throw new \Illuminate\Validation\ValidationException(
                    \Illuminate\Support\Facades\Validator::make([], []),
                    'Maker cannot settle their own approved request. A different approver is required.'
                );
            }
            $attributes['status'] = SETTLED;
            $withdrawRequestData = $this->withdrawRequestRepository->update(id: $id, data: $attributes);
            $this->withdrawRequestAcceptTransaction($withdrawRequest->user, $withdrawRequest->amount, $withdrawRequest);
            $this->withdrawRequestNotificationSendDriver(data: $data, withdrawRequestData: $withdrawRequestData);
        }

        if ($withdrawRequestData) {
            $this->logWithdrawAction($withdrawRequest, $data, $adminId);
        }

        return $withdrawRequestData;
    }

    public function multipleUpdate(array $data = []): void
    {
        if (array_key_exists('status', $data) && !is_null($data['status']) && array_key_exists('ids', $data) && (count($data['ids']) > 0)) {
            $adminId = auth()->user()->id;
            foreach ($data['ids'] as $id) {
                $withdrawRequest = $this->withdrawRequestRepository->findOne(id: $id, relations: ['user' => []]);
                if ($data['status'] == 'reverse') {
                    $attributes['status'] = PENDING;
                    $attributes['approved_by'] = null;
                } else {
                    $attributes['status'] = $data['status'];
                }

                if ($data['status'] == APPROVED) {
                    $attributes['approved_by'] = $adminId;
                }

                if ($data['status'] == SETTLED && $withdrawRequest->approved_by == $adminId) {
                    continue;
                }

                if (array_key_exists('rejection_cause', $data) && !is_null($data['rejection_cause'])) {
                    $attributes['rejection_cause'] = $data['rejection_cause'];
                }
                if (array_key_exists('approval_note', $data) && !is_null($data['approval_note'])) {
                    $attributes['approval_note'] = $data['approval_note'];
                }
                if (array_key_exists('denied_note', $data) && !is_null($data['denied_note'])) {
                    $attributes['denied_note'] = $data['denied_note'];
                }
                DB::beginTransaction();
                if ($data['status'] == DENIED) {
                    $this->withdrawRequestCancelTransaction($withdrawRequest->user, $withdrawRequest->amount, $withdrawRequest);
                }
                if ($data['status'] == SETTLED) {
                    $this->withdrawRequestAcceptTransaction($withdrawRequest->user, $withdrawRequest->amount, $withdrawRequest);
                }
                if ($data['status'] == 'reverse') {
                    $this->withdrawRequestReverseTransaction($withdrawRequest->user, $withdrawRequest->amount, $withdrawRequest);
                }
                $this->withdrawRequestRepository->update(id: $id, data: $attributes);
                DB::commit();
                $withdrawRequestData = $this->withdrawRequestRepository->findOne(id: $id, relations: ['user' => []]);
                $this->withdrawRequestNotificationSendDriver(data: $data, withdrawRequestData: $withdrawRequestData);
            }
        }
    }

    private function withdrawRequestNotificationSendDriver($data, $withdrawRequestData)
    {
        if ($data['status'] == DENIED) {
            $push = getNotification('withdraw_request_rejected');
            sendDeviceNotification(fcm_token: $withdrawRequestData->user->fcm_token,
                title: translate(key: $push['title'], locale: $withdrawRequestData?->user?->current_language_key),
                description: textVariableDataFormat(value: $push['description'], userName: $withdrawRequestData->user->first_name . ' ' . $withdrawRequestData->user->last_name, withdrawNote: $withdrawRequestData?->denied_note, locale: $withdrawRequestData?->user?->current_language_key),
                status: $push['status'],
                notification_type: 'withdraw_request',
                action: $push['action'],
                user_id: $withdrawRequestData?->user->id
            );
        } elseif ($data['status'] == SETTLED) {
            $push = getNotification('withdraw_request_settled');
            sendDeviceNotification(fcm_token: $withdrawRequestData->user->fcm_token,
                title: translate(key: $push['title'], locale: $withdrawRequestData?->user?->current_language_key),
                description: textVariableDataFormat(value: $push['description'], userName: $withdrawRequestData->user->first_name . ' ' . $withdrawRequestData->user->last_name, locale: $withdrawRequestData?->user?->current_language_key),
                status: $push['status'],
                notification_type: 'withdraw_request',
                action: $push['action'],
                user_id: $withdrawRequestData?->user->id
            );
        } elseif ($data['status'] == APPROVED) {
            $push = getNotification('withdraw_request_approved');
            sendDeviceNotification(fcm_token: $withdrawRequestData->user->fcm_token,
                title: translate(key: $push['title'], locale: $withdrawRequestData?->user?->current_language_key),
                description: textVariableDataFormat(value: $push['description'], userName: $withdrawRequestData->user->first_name . ' ' . $withdrawRequestData->user->last_name, locale: $withdrawRequestData?->user?->current_language_key),
                status: $push['status'],
                notification_type: 'withdraw_request',
                action: $push['action'],
                user_id: $withdrawRequestData?->user->id
            );
        } else {
            $push = getNotification('withdraw_request_reversed');
            sendDeviceNotification(fcm_token: $withdrawRequestData?->user->fcm_token,
                title: translate(key: $push['title'], locale: $withdrawRequestData?->user?->current_language_key),
                description: textVariableDataFormat(value: $push['description'], userName: $withdrawRequestData->user->first_name . ' ' . $withdrawRequestData->user->last_name, locale: $withdrawRequestData?->user?->current_language_key),
                status: $push['status'],
                notification_type: 'withdraw_request',
                action: $push['action'],
                user_id: $withdrawRequestData?->user->id
            );
        }
    }

    private function logWithdrawAction($withdrawRequest, $data, $adminId): void
    {
        try {
            $activityLog = new ActivityLog();
            $activityLog->edited_by = $adminId;
            $activityLog->before = [
                'withdraw_request_id' => $withdrawRequest->id,
                'previous_status' => $withdrawRequest->status,
                'amount' => $withdrawRequest->amount,
            ];
            $activityLog->after = [
                'action' => $data['status'],
                'note' => $data['approval_note'] ?? $data['denied_note'] ?? $data['rejection_cause'] ?? null,
            ];
            $activityLog->user_type = auth()->user()->user_type;
            $activityLog->logable_type = WithdrawRequest::class;
            $activityLog->logable_id = $withdrawRequest->id;
            $activityLog->save();
        } catch (\Exception $e) {
        }
    }

}

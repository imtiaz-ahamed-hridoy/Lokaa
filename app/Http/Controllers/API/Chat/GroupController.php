<?php

namespace App\Http\Controllers\API\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Chat\AddGroupMemberRequest;
use App\Http\Requests\API\Chat\CreateGroupRequest;
use App\Http\Requests\API\Chat\UpdateGroupRequest;
use App\Http\Resources\Chat\GroupResource;
use App\Models\Conversation;
use App\Services\Chat\GroupService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly GroupService $groupService,
    ) {}

    /**
     * Create a new group.
     */
    public function store(CreateGroupRequest $request): JsonResponse
    {
        try {
            $conversation = $this->groupService->createGroup(
                $request->validated(),
                $request->user()->id
            );

            return $this->success(
                'Group created successfully.',
                GroupResource::make($conversation),
                201
            );
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Update group details (name, image).
     */
    public function update(UpdateGroupRequest $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->authorize('updateGroup', $conversation);

            $updated = $this->groupService->updateGroup(
                $conversation,
                $request->validated()
            );

            return $this->success(
                'Group updated successfully.',
                GroupResource::make($updated)
            );
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Delete a group.
     */
    public function destroy(Request $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->authorize('delete', $conversation);

            $this->groupService->deleteGroup($conversation);

            return $this->success('Group deleted successfully.');
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Add a member to the group.
     */
    public function addMember(AddGroupMemberRequest $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->authorize('addMember', $conversation);

            $member = $this->groupService->addMember(
                $conversation,
                $request->input('user_id')
            );

            return $this->success(
                'Member added successfully.',
                [
                    'user_id' => $member->user_id,
                    'role' => $member->role,
                    'joined_at' => $member->joined_at,
                ],
                201
            );
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Remove a member from the group.
     */
    public function removeMember(Request $request, Conversation $conversation, int $user): JsonResponse
    {
        try {
            $this->authorize('removeMember', $conversation);

            $this->groupService->removeMember($conversation, $user);

            return $this->success('Member removed successfully.');
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Leave a group.
     */
    public function leave(Request $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->groupService->leaveGroup($conversation, $request->user()->id);

            return $this->success('You have left the group.');
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }
}

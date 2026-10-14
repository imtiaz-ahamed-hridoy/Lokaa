<?php

use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user1 = User::factory()->create([
        'name' => 'Alice User',
        'email' => 'alice@example.com',
        'email_verified_at' => now(),
    ]);

    $this->user2 = User::factory()->create([
        'name' => 'Bob User',
        'email' => 'bob@example.com',
        'email_verified_at' => now(),
    ]);

    $this->user3 = User::factory()->create([
        'name' => 'Charlie User',
        'email' => 'charlie@example.com',
        'email_verified_at' => now(),
    ]);
});

test('user can create a private conversation with another user', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->postJson('/api/conversations', [
        'type' => 'private',
        'member_ids' => [$this->user2->id],
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'message' => 'Conversation created successfully.',
    ]);
    expect($response->json('data.type'))->toBe('private');

    $this->assertDatabaseHas('conversations', [
        'type' => 'private',
        'created_by' => $this->user1->id,
    ]);
    $this->assertDatabaseHas('conversation_members', [
        'user_id' => $this->user1->id,
    ]);
    $this->assertDatabaseHas('conversation_members', [
        'user_id' => $this->user2->id,
    ]);
});

test('creating private conversation with existing user returns existing conversation without duplication', function () {
    $first = $this->actingAs($this->user1, 'sanctum')->postJson('/api/conversations', [
        'type' => 'private',
        'member_ids' => [$this->user2->id],
    ]);
    $firstId = $first->json('data.id');

    $second = $this->actingAs($this->user1, 'sanctum')->postJson('/api/conversations', [
        'type' => 'private',
        'member_ids' => [$this->user2->id],
    ]);

    $second->assertStatus(201);
    expect($second->json('data.id'))->toBe($firstId);
    expect(Conversation::where('type', 'private')->count())->toBe(1);
});

test('user can send a message and list messages in conversation', function () {
    // Create conversation
    $conversation = Conversation::create([
        'type' => 'private',
        'created_by' => $this->user1->id,
    ]);
    ConversationMember::create([
        'conversation_id' => $conversation->id,
        'user_id' => $this->user1->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);
    ConversationMember::create([
        'conversation_id' => $conversation->id,
        'user_id' => $this->user2->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);

    // Send message
    $response = $this->actingAs($this->user1, 'sanctum')->postJson('/api/messages', [
        'conversation_id' => $conversation->id,
        'message' => 'Hello Bob, nice to meet you!',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
    ]);
    expect($response->json('data.message'))->toBe('Hello Bob, nice to meet you!');

    // List messages
    $listResponse = $this->actingAs($this->user2, 'sanctum')->getJson("/api/conversations/{$conversation->id}/messages");
    $listResponse->assertStatus(200);
    expect($listResponse->json('data'))->toHaveCount(1);
});

test('unauthorized user cannot view messages of conversation they are not in', function () {
    $conversation = Conversation::create([
        'type' => 'private',
        'created_by' => $this->user1->id,
    ]);
    ConversationMember::create([
        'conversation_id' => $conversation->id,
        'user_id' => $this->user1->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);
    ConversationMember::create([
        'conversation_id' => $conversation->id,
        'user_id' => $this->user2->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);

    $response = $this->actingAs($this->user3, 'sanctum')->getJson("/api/conversations/{$conversation->id}/messages");
    $response->assertStatus(403);
});

test('user can mark conversation as read and check unread count', function () {
    $conversation = Conversation::create([
        'type' => 'private',
        'created_by' => $this->user1->id,
    ]);
    ConversationMember::create([
        'conversation_id' => $conversation->id,
        'user_id' => $this->user1->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);
    $bobMember = ConversationMember::create([
        'conversation_id' => $conversation->id,
        'user_id' => $this->user2->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);

    // Alice sends a message
    $msg = Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $this->user1->id,
        'type' => 'text',
        'message' => 'Unread message for Bob',
    ]);
    $conversation->update(['last_message_id' => $msg->id, 'last_message_at' => now()]);

    // Bob marks as read
    $markResponse = $this->actingAs($this->user2, 'sanctum')->postJson("/api/conversations/{$conversation->id}/read", [
        'message_id' => $msg->id,
    ]);
    $markResponse->assertStatus(200);
    $markResponse->assertJson(['success' => true]);

    $bobMember->refresh();
    expect($bobMember->last_read_message_id)->toBe($msg->id);
});

test('user can create a group conversation with multiple members', function () {
    $response = $this->actingAs($this->user1, 'sanctum')->postJson('/api/conversations', [
        'type' => 'group',
        'name' => 'Engineering Team',
        'member_ids' => [$this->user2->id, $this->user3->id],
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'message' => 'Group created successfully.',
    ]);
    expect($response->json('data.name'))->toBe('Engineering Team');
    expect($response->json('data.type'))->toBe('group');

    $this->assertDatabaseHas('conversations', [
        'name' => 'Engineering Team',
        'type' => 'group',
        'created_by' => $this->user1->id,
    ]);
    expect(ConversationMember::where('conversation_id', $response->json('data.id'))->count())->toBe(3);
});

test('user can trigger typing indicator', function () {
    $conversation = Conversation::create([
        'type' => 'private',
        'created_by' => $this->user1->id,
    ]);
    ConversationMember::create([
        'conversation_id' => $conversation->id,
        'user_id' => $this->user1->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);
    ConversationMember::create([
        'conversation_id' => $conversation->id,
        'user_id' => $this->user2->id,
        'role' => 'member',
        'joined_at' => now(),
    ]);

    $response = $this->actingAs($this->user1, 'sanctum')->postJson("/api/conversations/{$conversation->id}/typing");
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
});

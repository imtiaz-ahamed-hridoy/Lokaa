<?php

namespace Tests\Unit;

use App\Traits\ApiResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Unit tests for the ApiResponse trait.
 *
 * Covers: success(), ok(), error(), pagination(), safeError()
 */
class ApiResponseTest extends TestCase
{
    use ApiResponse;

    // -----------------------------------------------------------------------
    //  success()
    // -----------------------------------------------------------------------

    /** @test */
    public function success_returns_default_message_and_no_data_key()
    {
        $response = $this->success();
        $data = $response->getData(true);

        expect($response->getStatusCode())->toBe(200);
        expect($data['success'])->toBeTrue();
        expect($data['message'])->toBe('Request completed successfully.');
        expect($data)->not->toHaveKey('data');
    }

    /** @test */
    public function success_returns_custom_message()
    {
        $response = $this->success('Operation completed.');
        $data = $response->getData(true);

        expect($data['success'])->toBeTrue();
        expect($data['message'])->toBe('Operation completed.');
    }

    /** @test */
    public function success_includes_data_key_when_data_is_passed()
    {
        $response = $this->success('Created.', ['id' => 1, 'name' => 'Test']);
        $data = $response->getData(true);

        expect($data['data']['id'])->toBe(1);
        expect($data['data']['name'])->toBe('Test');
    }

    /** @test */
    public function success_includes_empty_array_data()
    {
        $response = $this->success('OK.', []);
        $data = $response->getData(true);

        expect($data)->toHaveKey('data');
        expect($data['data'])->toBe([]);
    }

    /** @test */
    public function success_returns_201_status_code()
    {
        $response = $this->success('Created.', ['id' => 1], 201);

        expect($response->getStatusCode())->toBe(201);
    }

    // -----------------------------------------------------------------------
    //  ok()  — alias for success()
    // -----------------------------------------------------------------------

    /** @test */
    public function ok_defaults_to_200_with_no_data_key()
    {
        $response = $this->ok();
        $data = $response->getData(true);

        expect($response->getStatusCode())->toBe(200);
        expect($data['success'])->toBeTrue();
        expect($data['message'])->toBe('Request completed successfully.');
        expect($data)->not->toHaveKey('data');
    }

    /** @test */
    public function ok_includes_data_when_provided()
    {
        $response = $this->ok('Done.', ['result' => true]);
        $data = $response->getData(true);

        expect($data['data']['result'])->toBeTrue();
    }

    // -----------------------------------------------------------------------
    //  error()
    // -----------------------------------------------------------------------

    /** @test */
    public function error_returns_default_400_with_no_field_errors()
    {
        $response = $this->error();
        $data = $response->getData(true);

        expect($response->getStatusCode())->toBe(400);
        expect($data['success'])->toBeFalse();
        expect($data['message'])->toBe('Invalid request.');
        expect($data['errors'])->toBeNull();
    }

    /** @test */
    public function error_returns_custom_message_and_status_code()
    {
        $response = $this->error('Not found.', 404);
        $data = $response->getData(true);

        expect($response->getStatusCode())->toBe(404);
        expect($data['success'])->toBeFalse();
        expect($data['message'])->toBe('Not found.');
    }

    /** @test */
    public function error_returns_401_for_authentication_failures()
    {
        $response = $this->error('Authentication required.', 401);

        expect($response->getStatusCode())->toBe(401);
    }

    /** @test */
    public function error_returns_403_for_forbidden()
    {
        $response = $this->error('You do not have permission.', 403);

        expect($response->getStatusCode())->toBe(403);
    }

    /** @test */
    public function error_returns_422_with_field_specific_errors()
    {
        $response = $this->error('Validation failed.', 422, [
            'email' => ['The email field is required.'],
            'name' => ['The name field is required.'],
        ]);
        $data = $response->getData(true);

        expect($response->getStatusCode())->toBe(422);
        expect($data['success'])->toBeFalse();
        expect($data['message'])->toBe('Validation failed.');
        expect($data['errors'])->toHaveKey('email');
        expect($data['errors'])->toHaveKey('name');
        expect($data['errors']['email'][0])->toBe('The email field is required.');
    }

    /** @test */
    public function error_returns_500_for_server_errors()
    {
        $response = $this->error('An unexpected server error occurred.', 500);
        $data = $response->getData(true);

        expect($response->getStatusCode())->toBe(500);
        expect($data['errors'])->toBeNull();
    }

    // -----------------------------------------------------------------------
    //  pagination()
    // -----------------------------------------------------------------------

    /** @test */
    public function pagination_returns_non_paginated_response()
    {
        $response = $this->pagination('List.', ['item' => 'value']);
        $data = $response->getData(true);

        expect($response->getStatusCode())->toBe(200);
        expect($data['success'])->toBeTrue();
        expect($data['data']['item'])->toBe('value');
        expect($data)->not->toHaveKey('pagination');
    }

    /** @test */
    public function pagination_returns_metadata_with_paginator()
    {
        $items = collect([
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ]);

        $paginator = new LengthAwarePaginator(
            $items,
            total: 10,
            perPage: 2,
            currentPage: 1,
            options: ['path' => 'http://localhost/api/items']
        );

        $response = $this->pagination('Items fetched.', $paginator);
        $data = $response->getData(true);

        expect($response->getStatusCode())->toBe(200);
        expect($data['success'])->toBeTrue();
        expect($data['message'])->toBe('Items fetched.');
        expect($data['data'])->toHaveCount(2);
        expect($data['data'][0]['name'])->toBe('Item 1');
        expect($data['pagination']['current_page'])->toBe(1);
        expect($data['pagination']['last_page'])->toBe(5);
        expect($data['pagination']['per_page'])->toBe(2);
        expect($data['pagination']['total'])->toBe(10);
    }

    /** @test */
    public function pagination_separates_data_and_paginator()
    {
        $items = collect([
            ['id' => 1, 'name' => 'Item 1'],
        ]);

        $paginator = new LengthAwarePaginator(
            $items,
            total: 3,
            perPage: 1,
            currentPage: 1,
            options: ['path' => 'http://localhost/api/items']
        );

        $customData = ['extra' => 'info'];
        $response = $this->pagination('Custom.', $customData, $paginator);
        $data = $response->getData(true);

        expect($data['data']['extra'])->toBe('info');
        expect($data['pagination']['total'])->toBe(3);
    }

    // -----------------------------------------------------------------------
    //  safeError()
    // -----------------------------------------------------------------------

    /** @test */
    public function safe_error_logs_exception_and_returns_safe_response()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'Something broke')
                    && isset($context['file'])
                    && isset($context['line'])
                    && isset($context['trace']);
            });

        $exception = new \RuntimeException('Something broke');

        $response = $this->safeError($exception, 'Please try again later.', 500);
        $data = $response->getData(true);

        expect($response->getStatusCode())->toBe(500);
        expect($data['success'])->toBeFalse();
        expect($data['message'])->toBe('Please try again later.');
        expect($data['errors'])->toBeNull();
    }

    /** @test */
    public function safe_error_uses_default_message_when_none_provided()
    {
        Log::shouldReceive('error')->once();

        $exception = new \Exception('DB error');

        $response = $this->safeError($exception);
        $data = $response->getData(true);

        expect($data['message'])->toBe('An unexpected server error occurred.');
        expect($response->getStatusCode())->toBe(500);
    }
}

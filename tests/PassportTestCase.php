<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use DateTime;
use DB;
use Illuminate\Support\Arr;
use Laravel\Passport\ClientRepository;

class PassportTestCase extends TestCase
{
    protected $headers = [
        'Accept-Language' => 'de, en',
    ];

    protected $scopes = ['*'];

    /**
     * @var User
     */
    protected $user;

    protected $token;

    protected $role = Role::ADMIN;

    protected $texts;

    public function setUp(): void
    {
        parent::setUp();

        // Set OAuth2 credentials
        $clientRepository = new ClientRepository();
        $client = $clientRepository->createPersonalAccessClient(
            null, 'Test Personal Access Client', '/'
        );
        DB::table('oauth_personal_access_clients')->insert([
            'client_id'  => $client->id,
            'created_at' => new DateTime,
            'updated_at' => new DateTime,
        ]);
        $this->user = $this->generateUser($this->role);
        $personalAccessTokenResult = $this->user->createToken('TestToken', $this->scopes);
        $this->token = $personalAccessTokenResult->token;
        $this->headers['Accept'] = 'application/json';
        $this->headers['Authorization'] = 'Bearer '.$personalAccessTokenResult->accessToken;
    }

    public function generateUser($role = Role::USER)
    {
        return User::factory()->create(['role' => $role]);
    }

    public function get($uri, array $headers = [])
    {
        return parent::get($uri, array_merge($this->headers, $headers));
    }

    public function getRequestWithoutAuthorization($uri, array $headers = [])
    {
        $headers = array_merge($this->headers, $headers);
        unset($headers['Authorization']);

        return parent::get($uri, $headers);
    }

    public function getJson($uri, array $headers = [])
    {
        return parent::getJson($uri, array_merge($this->headers, $headers));
    }

    public function post($uri, array $data = [], array $headers = [])
    {
        return parent::post($uri, $data, array_merge($this->headers, $headers));
    }

    public function postJson($uri, array $data = [], array $headers = [])
    {
        return parent::postJson($uri, $data, array_merge($this->headers, $headers));
    }

    public function put($uri, array $data = [], array $headers = [])
    {
        return parent::put($uri, $data, array_merge($this->headers, $headers));
    }

    public function putJson($uri, array $data = [], array $headers = [])
    {
        return parent::putJson($uri, $data, array_merge($this->headers, $headers));
    }

    public function patch($uri, array $data = [], array $headers = [])
    {
        return parent::patch($uri, $data, array_merge($this->headers, $headers));
    }

    public function patchJson($uri, array $data = [], array $headers = [])
    {
        return parent::patchJson($uri, $data, array_merge($this->headers, $headers));
    }

    public function delete($uri, array $data = [], array $headers = [])
    {
        return parent::delete($uri, $data, array_merge($this->headers, $headers));
    }

    public function deleteJson($uri, array $data = [], array $headers = [])
    {
        return parent::deleteJson($uri, $data, array_merge($this->headers, $headers));
    }

    public function initTexts()
    {
        $this->texts = $this->getJson('/api/texts')->json();
    }

    public function assertTextWithKeyIsGiven($object, $key, $optional = false): void
    {
        if ($optional && ! isset($object[$key])) {
            return;
        }
        static::assertIsString($object[$key]);
        static::assertNotEmpty($object[$key]);
        $textKey = $object[$key];
        if (! $this->texts) {
            $this->initTexts();
        }
        if (! Arr::has($this->texts, $textKey)) {
            static::fail('expecting that text with key:"'.$textKey.'"" is given');
        }
    }
}

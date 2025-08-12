<?php

namespace Database\Factories;

use App\Models\Salesforce;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesforceFactory extends Factory
{
    protected $model = Salesforce::class;

    public function definition()
    {
        return [
            'lead_id'                  => null,
            'contact_id'               => null,
            'account_id'               => null,
            'task_id'                  => null,
            'content_version_id'       => null,
            'content_document_id'      => null,
            'content_document_link_id' => null,
        ];
    }

    public function forUser($userId): self
    {
        return $this->state(['user_id' => $userId]);
    }

    public function registered($isLead = true): self
    {
        return $isLead ? $this->withLead() : $this->withContact()->withAccount();
    }

    public function downloadedSpecification($isLead = true): self
    {
        return $this->registered($isLead)
            ->withTask()
            ->withContentVersion()
            ->withContentDocument()
            ->withContentDocumentLink();
    }

    public function withLead(): self
    {
        return $this->state(fn () => ['lead_id' => $this->faker->uuid]);
    }

    public function withContact(): self
    {
        return $this->state(fn () => ['account_id' => $this->faker->uuid]);
    }

    public function withAccount(): self
    {
        return $this->state(fn () => ['contact_id' => $this->faker->uuid]);
    }

    public function withTask(): self
    {
        return $this->state(fn () => ['task_id' => $this->faker->uuid]);
    }

    public function withContentVersion(): self
    {
        return $this->state(fn () => ['content_version_id' => $this->faker->uuid]);
    }

    public function withContentDocument(): self
    {
        return $this->state(fn () => ['content_document_id' => $this->faker->uuid]);
    }

    public function withContentDocumentLink(): self
    {
        return $this->state(fn () => ['content_document_link_id' => $this->faker->uuid]);
    }
}

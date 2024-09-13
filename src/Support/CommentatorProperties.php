<?php

namespace Spatie\Comments\Support;

class CommentatorProperties
{
    public static function email(string $email): self
    {
        return new self(['email' => $email]);
    }

    protected function __construct(protected array $properties)
    {
    }

    public function name(?string $name): self
    {
        $this->properties['name'] = $name;

        return $this;
    }

    public function url(string $url): self
    {
        $this->properties['url'] = $url;

        return $this;
    }

    public function avatar(string $avatar): self
    {
        $this->properties['avatar'] = $avatar;

        return $this;
    }

    public function add(string $name, mixed $value): self
    {
        $this->properties[$name] = $value;

        return $this;
    }

    public function __get($name): mixed
    {
        return $this->properties[$name] ?? null;
    }
}

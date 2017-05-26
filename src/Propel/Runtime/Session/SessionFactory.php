<?php

namespace Propel\Runtime\Session;

use Propel\Runtime\Configuration;

class SessionFactory
{
    /**
     * @var Session
     */
    protected $currentSession;

    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Session
     */
    public function build(): Session
    {
        return $this->currentSession = new Session($this->configuration);
    }

    /**
     * @return Session
     */
    public function getCurrentSession(): Session
    {
        if (!$this->currentSession) {
            return $this->build();
        }

        return $this->currentSession;
    }
}

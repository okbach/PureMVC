<?php

namespace App\helper;

class response
{
    private $status;
    private $result;
    private $errorsValidation;
    private $errorsDatabase;
    private $errorsLogic;
    private $translator;

    public function __construct(string $status, $result = null, array $errorsValidation = [], $errorsDatabase = null, array $errorsLogic = [], $translator = null)
    {
        $this->status = $status;
        $this->result = $result;
        $this->errorsValidation = $errorsValidation;
        $this->errorsDatabase = $errorsDatabase;
        $this->errorsLogic = $errorsLogic;
        $this->translator = $translator;
    }

    public function getJson(): string
    {
        $errors = [];
        if (!empty($this->errorsValidation)) {
            $errors = $this->errorsValidation;
        }

        if (!empty($this->errorsDatabase)) {
            if ($this->translator !== null) {
                $errors = $this->translator->trans($this->errorsDatabase, [], null, $this->translator->getLocale());
            } else {
                $errors = $this->errorsDatabase;
            }
        }

        if (!empty($this->errorsLogic)) {
            $errors = $this->errorsLogic;
        }

        return json_encode([
            'status' => $this->status,
            'result' => $this->result,
            'errors' => $errors,
        ]);
    }

    public function send(): void
    {
        header('Content-Type: application/json');
        echo $this->getJson();
        exit;
    }
}
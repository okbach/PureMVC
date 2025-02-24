<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* email/resetpassword.twig */
class __TwigTemplate_6f252f35ed0b4ee98265744eb59453fd extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<!DOCTYPE html>
<html lang=\"";
        // line 2
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["language"] ?? null), "html", null, true);
        yield "\" dir=\"%dir%\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["subject"] ?? null), "html", null, true);
        yield "</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            direction: %dir%;
            text-align: right;
        }
        .email-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .email-header {
            text-align: center;
            color: #007BFF;
        }
        .email-button {
            display: inline-block;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
        }
        .email-footer {
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        p {
            direction: %dir%;
        }
    </style>
</head>
<body>
    <div class=\"email-container\">
        <h1 class=\"email-header\">";
        // line 48
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["subject"] ?? null), "html", null, true);
        yield "</h1>
        <p>";
        // line 49
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["greeting"] ?? null), "html", null, true);
        yield "</p>
        <p>";
        // line 50
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["instruction"] ?? null), "html", null, true);
        yield "</p>
        <p style=\"text-align: center;\">
            <a href=\"";
        // line 52
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["url"] ?? null), "html", null, true);
        yield "\" class=\"email-button\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["button_text"] ?? null), "html", null, true);
        yield "</a>
        </p>
        <p>";
        // line 54
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["ignore"] ?? null), "html", null, true);
        yield "</p>
        <p class=\"email-footer\">";
        // line 55
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["footer"] ?? null), "a", [], "any", false, false, false, 55), "html", null, true);
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["company_name"] ?? null), "html", null, true);
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["footer"] ?? null), "b", [], "any", false, false, false, 55), "html", null, true);
        yield "</p>
    </div>
</body>
</html>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "email/resetpassword.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  121 => 55,  117 => 54,  110 => 52,  105 => 50,  101 => 49,  97 => 48,  52 => 6,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"{{ language }}\" dir=\"%dir%\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{{ subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            direction: %dir%;
            text-align: right;
        }
        .email-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .email-header {
            text-align: center;
            color: #007BFF;
        }
        .email-button {
            display: inline-block;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
        }
        .email-footer {
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        p {
            direction: %dir%;
        }
    </style>
</head>
<body>
    <div class=\"email-container\">
        <h1 class=\"email-header\">{{ subject }}</h1>
        <p>{{ greeting }}</p>
        <p>{{ instruction }}</p>
        <p style=\"text-align: center;\">
            <a href=\"{{ url }}\" class=\"email-button\">{{ button_text }}</a>
        </p>
        <p>{{ ignore }}</p>
        <p class=\"email-footer\">{{ footer.a }} {{ company_name }} {{ footer.b }}</p>
    </div>
</body>
</html>", "email/resetpassword.twig", "C:\\xampp\\htdocs\\mymvc\\view\\templates\\email\\resetpassword.twig");
    }
}

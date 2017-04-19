<?php

/* index.html.twig */
class __TwigTemplate_345724a3e5fff7106826ea98ccf615f47c78f9ee29d53ed5c61eb5d594ae4705 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        $this->parent = $this->loadTemplate("layout.html.twig", "index.html.twig", 1);
        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
        // line 4
        echo "  <h1>Slim</h1>
  <div>a microframework for PHP</div>
  ";
        // line 6
        if (($context["name"] ?? null)) {
            // line 7
            echo "    <p>Hello ";
            echo twig_escape_filter($this->env, twig_capitalize_string_filter($this->env, ($context["name"] ?? null)), "html", null, true);
            echo "!!</p>
    <p><a href=\"";
            // line 8
            echo twig_escape_filter($this->env, $this->env->getExtension('Slim\Views\TwigExtension')->pathFor("home"), "html", null, true);
            echo "\">Home</a></p>
  ";
        } else {
            // line 10
            echo "    <p>Try <a href=\"http://www.slimframework.com\">SlimFramework</a></p>
    <p><a href=\"";
            // line 11
            echo twig_escape_filter($this->env, $this->env->getExtension('Slim\Views\TwigExtension')->pathFor("name", array("name" => "cyle")), "html", null, true);
            echo "\">Cyle</a></p>
  ";
        }
    }

    public function getTemplateName()
    {
        return "index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  50 => 11,  47 => 10,  42 => 8,  37 => 7,  35 => 6,  31 => 4,  28 => 3,  11 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("{% extends \"layout.html.twig\" %}

{% block content %}
  <h1>Slim</h1>
  <div>a microframework for PHP</div>
  {% if name %}
    <p>Hello {{ name|capitalize }}!!</p>
    <p><a href=\"{{ path_for('home') }}\">Home</a></p>
  {% else %}
    <p>Try <a href=\"http://www.slimframework.com\">SlimFramework</a></p>
    <p><a href=\"{{ path_for('name', { 'name': 'cyle' }) }}\">Cyle</a></p>
  {% endif %}
{% endblock %}
", "index.html.twig", "/Users/JaceRider/Desktop/terminus_remote/templates/index.html.twig");
    }
}

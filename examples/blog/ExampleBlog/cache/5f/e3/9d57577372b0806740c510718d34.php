<?php

/* layouts/_master_one_column.twig */
class __TwigTemplate_5fe39d57577372b0806740c510718d34 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'html_head' => array($this, 'block_html_head'),
            'topbar' => array($this, 'block_topbar'),
            'page_header' => array($this, 'block_page_header'),
            'all_content' => array($this, 'block_all_content'),
            'footer' => array($this, 'block_footer'),
            'page_scripts' => array($this, 'block_page_scripts'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">

";
        // line 4
        $this->displayBlock('html_head', $context, $blocks);
        // line 7
        echo "
<body>

";
        // line 10
        $this->displayBlock('topbar', $context, $blocks);
        // line 15
        echo "
<div class=\"container\">

\t<div class=\"content\">

\t\t";
        // line 20
        if (array_key_exists("page_header", $context)) {
            // line 21
            echo "\t\t";
            $this->displayBlock('page_header', $context, $blocks);
            // line 24
            echo "\t\t";
        }
        // line 25
        echo "
\t\t<div class=\"row\">
\t\t\t<div class=\"span14\">
\t\t\t\t";
        // line 28
        $this->displayBlock('all_content', $context, $blocks);
        // line 32
        echo "\t\t\t</div>
\t\t</div>
\t</div>

\t";
        // line 36
        $this->displayBlock('footer', $context, $blocks);
        // line 39
        echo "
</div>
";
        // line 41
        $this->displayBlock('page_scripts', $context, $blocks);
        // line 47
        echo "</body>
</html>";
    }

    // line 4
    public function block_html_head($context, array $blocks = array())
    {
        // line 5
        $this->env->loadTemplate("layouts/_master.html_head.twig")->display($context);
    }

    // line 10
    public function block_topbar($context, array $blocks = array())
    {
        // line 11
        if (array_key_exists("topbar", $context)) {
            // line 12
            $this->env->loadTemplate("layouts/_master.topbar.twig")->display($context);
        }
    }

    // line 21
    public function block_page_header($context, array $blocks = array())
    {
        // line 22
        echo "\t\t";
        $this->env->loadTemplate("layouts/_master.page_header.twig")->display($context);
        // line 23
        echo "\t\t";
    }

    // line 28
    public function block_all_content($context, array $blocks = array())
    {
        // line 29
        echo "\t\t\t\t<h2>";
        echo twig_escape_filter($this->env, $this->getContext($context, "main_content_title"), "html", null, true);
        echo "</h2>
\t\t\t\t";
        // line 30
        echo twig_escape_filter($this->env, $this->getContext($context, "main_content"), "html", null, true);
        echo "
\t\t\t\t";
    }

    // line 36
    public function block_footer($context, array $blocks = array())
    {
        // line 37
        echo "\t";
        $this->env->loadTemplate("layouts/_master.page_footer.twig")->display($context);
        // line 38
        echo "\t";
    }

    // line 41
    public function block_page_scripts($context, array $blocks = array())
    {
        // line 42
        echo "<script type=\"text/javascript\">
\t\$(function(){
\t});
</script>
";
    }

    public function getTemplateName()
    {
        return "layouts/_master_one_column.twig";
    }

    public function isTraitable()
    {
        return false;
    }
}

<?php namespace Verbant\Livewire\Classes;

use Twig\Node\Node as TwigNode;
use Twig\Compiler as TwigCompiler;

/**
 * Represents a Livewire component node
 *
 * @package 
 * @author Wim Verhoogt
 */
class ComponentNode extends TwigNode
{
  public function __construct(TwigNode $nodes, $paramNames, $lineno, $tag = 'partial')
  {
    parent::__construct(['nodes' => $nodes], ['names' => $paramNames], $lineno, $tag);
  }

  /**
   * Compiles the node to PHP.
   *
   * @param TwigCompiler $compiler A TwigCompiler instance
   */
  public function compile(TwigCompiler $compiler)
  {
    $compiler->addDebugInfo($this);

    $compiler->write("\$context['__livewire_component_params'] = [];\n");

    for ($i = 1; $i < count($this->getNode('nodes')); $i++) {
      $compiler->write("\$context['__livewire_component_params']['".$this->getAttribute('names')[$i-1]."'] = ");
      $compiler->subcompile($this->getNode('nodes')->getNode($i));
      $compiler->write(";\n");
    }

    $compiler
      ->write("echo \$this->env->getExtension('Verbant\Livewire\Classes\Extension')->componentFunction(")
      ->subcompile($this->getNode('nodes')->getNode(0))
      ->write(", \$context['__livewire_component_params']")
      ->write(", true")
      ->write(");\n")
    ;

    $compiler->write("unset(\$context['__livewire_component_params']);\n");
  }
}

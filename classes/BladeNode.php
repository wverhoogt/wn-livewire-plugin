<?php namespace Verbant\Livewire\Classes;

use Twig\Node\Node as TwigNode;
use Twig\Compiler as TwigCompiler;
use Twig\Node\Expression\AbstractExpression;
use Livewire\LivewireBladeDirectives;

/**
 * Represents a Livewire component node
 *
 * @package 
 * @author Wim Verhoogt
 */
class BladeNode extends TwigNode
{
  public function __construct(string $component, AbstractExpression $variables, $lineno, $tag = 'partial')
  {
    $nodes = ['variables' => $variables];
    parent::__construct($nodes, ['component' => $component], $lineno, $tag);
  }

  /**
   * Compiles the node to PHP.
   *
   * @param TwigCompiler $compiler A TwigCompiler instance
   */
  public function compile(TwigCompiler $compiler)
  {
    $compiler->addDebugInfo($this);

    $component = $this->getAttribute('component');
    $expr = $this->getNode('variables');
    $compiler
      ->write('$_instance = $context["_instance"] ?? null;', "\n")
      ->write('$_vars = ')->subcompile($expr)->raw(";\n")
      ->write("?>\n")
      ->write(LivewireBladeDirectives::livewire("'$component', \$_vars"))
      ->write("<?php\n")
    ;
  }
}

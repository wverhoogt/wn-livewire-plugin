<?php namespace Verbant\Livewire\Classes;

use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Node as TwigNode;
use Twig\Token as TwigToken;
use Twig\TokenParser\AbstractTokenParser as TwigTokenParser;
use Twig\Error\SyntaxError as TwigErrorSyntax;

/**
 * @package winter\wn-cms-module
 * @author Wim verhoogt
 */
class BladeTokenParser extends TwigTokenParser
{
  /**
   * Parses a token and returns a node.
   *
   * @param TwigToken $token A TwigToken instance
   * @return TwigNode A TwigNode instance
   */
  public function parse(TwigToken $token)
  {
    $lineno = $token->getLine();
    $stream = $this->parser->getStream();
    $component = $this->parser->getStream()->next();

    if (!$component->test(TwigToken::NAME_TYPE) && !$component->test(TwigToken::STRING_TYPE)) {
      throw new TwigErrorSyntax(
        sprintf(
          'Unexpected token "%s"%s ("%s" or "%s" expected).',
          TwigToken::typeToEnglish($component->getType()),
          $componentNameToken->getValue() ? sprintf(' of value "%s"', $component->getValue()) : '',
          TwigToken::typeToEnglish(TwigToken::NAME_TYPE),
          TwigToken::typeToEnglish(TwigToken::STRING_TYPE)
        ),
        $component->getLine(),
        $this->parser->getStream()->getSourceContext()
      );
    }
    if ($this->parser->getStream()->nextIf(TwigToken::PUNCTUATION_TYPE, ',')) {
      $variables = $this->parser->getExpressionParser()->parseExpression();
    } else {
      $variables = new ArrayExpression([], 0);
    }
    $this->parser->getStream()->expect(TwigToken::BLOCK_END_TYPE);
    return new BladeNode($component->getValue(), $variables, $token->getLine(), $this->getTag());
  }

  /**
   * Gets the tag name associated with this token parser.
   *
   * @return string The tag name
   */
  public function getTag()
  {
    return 'lwblade';
  }
}

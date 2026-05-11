<?php

namespace SimpleMappr\Twig;

use Twig\Error\SyntaxError;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Parses {% trans %} ... {% endtrans %} and inline {% trans "..." %} tags.
 *
 * Supports both forms used in the SimpleMappr templates:
 *   {% trans "Some message" %}
 *   {% trans %}Hello {{ name }}{% endtrans %}
 *
 * Plural / domain / notes forms from the legacy twig/extensions package are
 * not implemented because the existing templates do not use them.
 */
class TransTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        // Inline form: {% trans "string" %}
        if (!$stream->test(Token::BLOCK_END_TYPE)) {
            $expr = $this->parser->getExpressionParser()->parseExpression();
            $stream->expect(Token::BLOCK_END_TYPE);

            return new TransNode($expr, null, $lineno, $this->getTag());
        }

        // Block form: {% trans %}...{% endtrans %}
        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideTransFork']);
        $stream->expect(Token::BLOCK_END_TYPE);

        [$msg, $vars] = $this->compileBody($body);

        return new TransNode($msg, $vars, $lineno, $this->getTag());
    }

    public function decideTransFork(Token $token): bool
    {
        return $token->test(['endtrans']);
    }

    public function getTag(): string
    {
        return 'trans';
    }

    /**
     * Walk the body and turn it into a gettext message string + variable map.
     *
     * Text nodes become literal text; print nodes ({{ name }}) become %name%
     * placeholders that are interpolated by strtr at runtime.
     *
     * @return array{0: ConstantExpression, 1: array<string, Node>}
     */
    private function compileBody(Node $body): array
    {
        $msg = '';
        $vars = [];

        foreach ($this->iterate($body) as $node) {
            if ($node instanceof TextNode) {
                $msg .= $node->getAttribute('data');
                continue;
            }
            if ($node instanceof \Twig\Node\PrintNode) {
                $expr = $node->getNode('expr');
                if ($expr instanceof \Twig\Node\Expression\NameExpression) {
                    $name = $expr->getAttribute('name');
                    $msg .= '%' . $name . '%';
                    $vars[$name] = $expr;
                    continue;
                }
                // Fallback: synthesize a placeholder for arbitrary expressions.
                $key = 'var' . count($vars);
                $msg .= '%' . $key . '%';
                $vars[$key] = $expr;
                continue;
            }

            throw new SyntaxError(
                sprintf('Unsupported node type "%s" inside {%% trans %%}.', get_class($node)),
                $node->getTemplateLine()
            );
        }

        return [new ConstantExpression(trim($msg), $body->getTemplateLine()), $vars];
    }

    /**
     * @return iterable<Node>
     */
    private function iterate(Node $node): iterable
    {
        // A subparse() result is wrapped in a Node holding children.
        if ($node instanceof TextNode || $node instanceof \Twig\Node\PrintNode) {
            yield $node;
            return;
        }
        foreach ($node as $child) {
            yield from $this->iterate($child);
        }
    }
}

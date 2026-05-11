<?php

namespace SimpleMappr\Twig;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

/**
 * Compiles a parsed {% trans %} tag into PHP that echoes gettext($msg)
 * (with strtr for placeholder interpolation when needed).
 */
class TransNode extends Node
{
    /**
     * @param array<string, Node>|null $vars
     */
    public function __construct(AbstractExpression $expr, ?array $vars, int $lineno, string $tag)
    {
        $nodes = ['expr' => $expr];
        parent::__construct($nodes, ['vars' => $vars ?? []], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $vars = $this->getAttribute('vars');

        if (!$vars) {
            $compiler
                ->write('echo gettext(')
                ->subcompile($this->getNode('expr'))
                ->raw(");\n");
            return;
        }

        $compiler
            ->write('echo strtr(gettext(')
            ->subcompile($this->getNode('expr'))
            ->raw('), [');

        $first = true;
        foreach ($vars as $key => $expr) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $first = false;
            $compiler
                ->repr('%' . $key . '%')
                ->raw(' => ')
                ->subcompile($expr);
        }
        $compiler->raw("]);\n");
    }
}

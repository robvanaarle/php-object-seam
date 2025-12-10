<?php

namespace PHPObjectSeam\Code;

final class CodeBlock
{
    private $codeLines = [];

    public function __construct(array $codeLines = [])
    {
        $this->codeLines = $codeLines;
    }

    public function add(string $code): CodeBlock
    {
        if (count($this->codeLines) > 0) {
            $this->codeLines[count($this->codeLines) - 1] .= $code;
        } else {
            $this->codeLines[] = $code;
        }
        return $this;
    }

    public function addLine(string $code = '', int $indentLevel = 0): CodeBlock
    {
        $this->codeLines[] = $this->indent($code, $indentLevel);
        return $this;
    }

    public function prepend(string $code): CodeBlock
    {
        if (count($this->codeLines) === 0) {
            return $this->addLine($code);
        }

        $this->codeLines[0] = $code . $this->codeLines[0];
        return $this;
    }

    public function prependLine(string $code = '', int $indentLevel = 0): CodeBlock
    {
        array_unshift($this->codeLines, $this->indent($code, $indentLevel));
        return $this;
    }

    public function addLineIndented(string $code = ''): CodeBlock
    {
        return $this->addLine($code, 1);
    }

    public function merge(CodeBlock $otherBlock, int $indentLevel = 0): CodeBlock
    {
        foreach ($otherBlock->codeLines as $line) {
            $this->addLine($line, $indentLevel);
        }
        return $this;
    }

    public function mergeIndented(CodeBlock $otherBlock): CodeBlock
    {
        return $this->merge($otherBlock, 1);
    }

    public function mergeInline(CodeBlock $otherBlock): CodeBlock
    {
        foreach ($otherBlock->codeLines as $i => $line) {
            if ($i === 0) {
                $this->add($line);
            } else {
                $this->addLine($line);
            }
        }
        return $this;
    }

    private function indent(string $line, int $level): string
    {
        $indentation = str_repeat('  ', $level); // 2 spaces per level
        return $indentation . $line;
    }

    public function toString(string $separator = "\n"): string
    {
        return implode($separator, $this->codeLines);
    }

    public function lineCount(): int
    {
        return count($this->codeLines);
    }
}

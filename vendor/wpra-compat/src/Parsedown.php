<?php

class Parsedown
{
    protected $breaksEnabled = false;
    protected $markupEscaped = false;

    public function setBreaksEnabled($breaksEnabled) { $this->breaksEnabled = (bool) $breaksEnabled; return $this; }
    public function setMarkupEscaped($markupEscaped) { $this->markupEscaped = (bool) $markupEscaped; return $this; }

    public function text($text)
    {
        $text = (string) $text;
        if ($this->markupEscaped) {
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }
        $text = preg_replace('/^###\s*(.+)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^##\s*(.+)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^#\s*(.+)$/m', '<h1>$1</h1>', $text);
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $text);
        return $this->breaksEnabled ? nl2br($text) : $text;
    }
}

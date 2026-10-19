<?php

namespace Tests\Unit;

use Illuminate\Mail\Markdown;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\MarkdownConverter;
use PHPUnit\Framework\TestCase;

class CommonMarkSecurityTest extends TestCase
{
    public function testLaravelMailMarkdownStillRendersLinksAndEmphasis()
    {
        $html = (string) Markdown::parse('Hello **world**. [Visit](https://example.com)');

        $this->assertStringContainsString('<strong>world</strong>', $html);
        $this->assertStringContainsString('<a href="https://example.com">Visit</a>', $html);
    }

    public function testOverlongReferenceLabelsRemainLiteral()
    {
        $converter = new CommonMarkConverter();
        $label = 'a'.str_repeat(' ', 998).'b';
        $html = (string) $converter->convert('['.$label."]\n\n[a b]: /target");

        $this->assertStringNotContainsString('<a ', $html);
    }

    public function testAttributesAndSmartPunctuationRemainCompatible()
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AttributesExtension());
        $environment->addExtension(new SmartPunctExtension());
        $converter = new MarkdownConverter($environment);
        $html = (string) $converter->convert('"Hello" **world**{.first}{.second}');

        $this->assertStringContainsString('“Hello”', $html);
        $this->assertStringContainsString('<strong class="first second">world</strong>', $html);
    }
}

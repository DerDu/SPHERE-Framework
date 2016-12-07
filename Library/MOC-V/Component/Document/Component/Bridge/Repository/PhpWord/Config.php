<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository\PhpWord;

use MOC\V\Component\Document\Component\Bridge\Bridge;
use MOC\V\Component\Document\Component\IBridgeInterface;

/**
 * Class Config
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository\PhpWord
 */
abstract class Config extends Bridge implements IBridgeInterface
{

    /** @var null|\PhpOffice\PhpWord\PhpWord $Source */
    protected $Source = null;
}

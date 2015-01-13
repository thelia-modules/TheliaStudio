<?php
/*************************************************************************************/
/* This file is part of the Thelia package.                                          */
/*                                                                                   */
/* Copyright (c) OpenStudio                                                          */
/* email : dev@thelia.net                                                            */
/* web : http://www.thelia.net                                                       */
/*                                                                                   */
/* For the full copyright and license information, please view the LICENSE.txt       */
/* file that was distributed with this source code.                                  */
/*************************************************************************************/

namespace TheliaStudio\Tests\Generator;

use TheliaStudio\Generator\RawCopyGenerator;
use TheliaStudio\Tests\GeneratorTestCase;

/**
 * Class RawCopyGeneratorTest
 * @package TheliaStudio\Tests\Generator
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class RawCopyGeneratorTest extends GeneratorTestCase
{
    public function testRawCopy()
    {
        $generator = new RawCopyGenerator();
        $generator->doGenerate($this->event);

        $this->assertFileExists($this->getStreamPath("Config/insert.sql")); // easy
    }
}

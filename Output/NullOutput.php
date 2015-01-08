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

namespace TheliaStudio\Output;

use Symfony\Component\Console\Output\NullOutput as BaseNullOutput;

/**
 * Class NullOutput
 * @package TheliaStudio\Output
 * @author Benjamin Perche <bperche9@gmail.com>
 */
class NullOutput extends BaseNullOutput
{
    // ConsoleOutput compatibility
    public function renderBlock()
    {
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 11/5/18
 * Time: 20:45
 */

namespace Rodenastyle\StreamParser\Test;

use PHPUnit\Framework\TestCase as BaseTest;
use Rodenastyle\StreamParser\Test\Contracts\ElementToCollectionTransformation;
use Rodenastyle\StreamParser\Test\Contracts\MainElementsDetection;

abstract class TestCase extends BaseTest
implements
	MainElementsDetection,
	ElementToCollectionTransformation
{}
<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 11/10/17
 * Time: 1:18 AM.
 */

namespace PhpJunior\LaravelVideoChat\Tests;

use GrahamCampbell\TestBench\AbstractPackageTestCase;
use PhpJunior\LaravelVideoChat\LaravelVideoChatServiceProvider;

abstract class TestCase extends AbstractPackageTestCase
{
    /**
     * Get the service provider class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return string
     */
    protected function getServiceProviderClass($app)
    {
        return LaravelVideoChatServiceProvider::class;
    }
}

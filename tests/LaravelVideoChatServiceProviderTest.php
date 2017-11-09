<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 11/10/17
 * Time: 1:20 AM.
 */

namespace PhpJunior\LaravelVideoChat\Tests;

use GrahamCampbell\TestBenchCore\ServiceProviderTrait;
use PhpJunior\LaravelVideoChat\Services\Chat;

class LaravelVideoChatServiceProviderTest extends TestCase
{
    use ServiceProviderTrait;

    public function testChatIsInjectable()
    {
        $this->assertIsInjectable(Chat::class);
    }
}

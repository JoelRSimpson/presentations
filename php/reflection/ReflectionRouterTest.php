<?php
/**
 * A prototype of a request router using DocBlock annotations and reflection
 *
 * @created    9/11/13 5:23 PM
 * @author     Joel Simpson <joel.simpson@gmail.com>
 *
 * This is free and unencumbered software released into the public domain.
 *
 * Anyone is free to copy, modify, publish, use, compile, sell, or
 * distribute this software, either in source code form or as a compiled
 * binary, for any purpose, commercial or non-commercial, and by any
 * means.
 * In jurisdictions that recognize copyright laws, the author or authors
 * of this software dedicate any and all copyright interest in the
 * software to the public domain. We make this dedication for the benefit
 * of the public at large and to the detriment of our heirs and
 * successors. We intend this dedication to be an overt act of
 * relinquishment in perpetuity of all present and future rights to this
 * software under copyright law.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * For more information, please refer to <http://unlicense.org/>
 */

class Routable
{

}

/**
 * Class UserController
 */
class UserController extends Routable
{
    /**
     * @url /user/index
     */
    function indexAction()
    {
        return "User/Index";
    }

    /**
     * @url /user/profile
     */
    function userProfileAction()
    {
        return "User/Profile";

    }
}

/**
 * Class NewsController
 */
class NewsController extends Routable
{
    /**
     * @url /news
     * @return string
     */
    function indexAction()
    {
        return "News/Index";

    }
}

/**
 * Class DefaultController
 */
class DefaultController extends Routable
{

    /**
     * @url /default/index
     * @return string
     */
    function indexAction()
    {
        return "Default/Index";
    }

    /**
     * @url default/notfound
     * @return string
     */
    function notFoundAction()
    {
        return "no route found";
    }
}

/**
 * Class Router
 */
class Router
{
    /**
     * The list of classes that can be routed to
     *
     * @var array
     */
    private static $routableClasses = array();

    /**
     * Route a request to the appropriate class and handler method
     *
     * @param $url
     *
     * @return string
     */
    static function route($url)
    {
        if (count(self::$routableClasses) == 0) {
            self::loadRoutableClasses();
        }

        foreach (self::$routableClasses as $className) {

            $classReflection = new ReflectionClass($className);

            foreach ($classReflection->getMethods() as $methodReflection) {

                $docBlock = $methodReflection->getDocComment();
                preg_match_all('/@url\s+[ \t]*\/?(\S*)/s', $docBlock, $matches, PREG_SET_ORDER);
                if (!$matches) {
                    continue;
                }
                foreach ($matches[0] as $route) {
                    if (self::cleanUrl($url) == self::cleanUrl($route)) {

                        $targetClass = $classReflection->newInstance();

                        /**
                         * Method that has a matching @url annotation
                         */
                        $targetMethod = $methodReflection->getShortName();
                        return $targetClass->$targetMethod();
                    }
                }

            }
        }

        /**
         * Nothing found so we do the pre-defined not found route
         */
        return (new DefaultController())->notFoundAction();
    }

    /**
     * A class potentially has rout-able methods if it is a subclass of our Routable class
     */
    private static function loadRoutableClasses()
    {
        foreach (get_declared_classes() as $className) {
            if (is_subclass_of($className, 'Routable')) {
                self::$routableClasses[] = $className;
            }
        }
    }

    /**
     * normalize url's for more lenient matching
     *
     * @param $url
     *
     * @return string
     */
    private static function cleanUrl($url)
    {
        return strtolower(trim($url, "\\/"));
    }
}

/**
 * Class ReflectionTest
 */
class ReflectionRouterTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testRouting()
    {

        $tests = array(
            '/user/index' => 'User/Index',
            '/user/profile' => 'User/Profile',
            '/news' => 'News/Index',
            '/afsdooasofdsa/asdf' => 'no route found'
        );

        foreach ($tests as $url => $result) {
            echo Router::route($url) . "\n";
            $this->assertEquals($result, Router::route($url));
        }


    }

}

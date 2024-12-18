<?php
namespace SLiMS\Debug;

use Symfony\Component\VarDumper\Dumper\ContextualizedDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use SLiMS\Debug\Dumper\Cli;
use SLiMS\Debug\Dumper\Web;

class VarDumper
{
    /**
     * @var callable|null
     */
    private static $handler;

    /**
     * @param string|null $label
     *
     * @return mixed
     */
    public static function dump(mixed $var, string $label = null)
    {
        if (null === self::$handler) {
            self::setHandler();
        }

        return (self::$handler)($var, $label);
    }

    public static function setHandler(string $handlerClass = '')
    {
        $dumper = $handlerClass === '' ? (isCli() ? new Cli : new Web) : new $handlerClass;
        $dumper = new ContextualizedDumper($dumper, [new SourceContextProvider()]);
        $cloner = new VarCloner;
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);

        self::$handler = function($var, $label) use($dumper,$cloner) {
            $var = $cloner->cloneVar($var);

            if (null !== $label) {
                $var = $var->withContext(['label' => $label]);
            }

            $dumper->dump($var);
        };
    }
}
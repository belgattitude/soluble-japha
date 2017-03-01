<?php

namespace Soluble\Japha\Bridge\Exception;

interface JavaExceptionInterface
{
    /**
     * Return Java cause. The cause differs from getMessage() as
     * it does not include the bridge error message.
     *
     * For example:
     * - message = Invoke failed: [[o:String]]->anInvalidMethod. java.lang.NoSuchMethodException: anInvalidMethod()
     * - cause = java.lang.NoSuchMethodException: anInvalidMethod()
     *
     * @return string
     */
    public function getCause();

    /**
     * Return Java stack trace as string.
     *
     * @return string
     */
    public function getStackTrace();

    /**
     * Return the originating Java Exception
     * class name (FQDN).
     *
     * @return string ava exception class name (FQDN)
     */
    public function getJavaClassName();
}

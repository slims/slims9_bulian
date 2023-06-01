<?php
/**
 * CSP (Content Security Policy)
 * 
 * fell free to add your custom CSP.
 * 
 * original code by 2022 Hendro Wicaksono (hendrowicaksono@yahoo.com)
 * modified by Drajat Hasan (drajathasan20@gmail.com)
 */
return [
    "base-uri 'self'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' *.google.com *.gstatic.com",
    "style-src 'self' 'unsafe-inline' *.bootstrapcdn.com *.googleapis.com",
    "img-src 'self' data:",
    "connect-src 'self' slims.web.id",
    "frame-src 'self' *.google.com",
    "font-src 'self' *.gstatic.com",
    "media-src 'self'",
    "object-src 'self'",
    "manifest-src 'self'", 
    "worker-src 'self'",
    "frame-ancestors 'self'"
];
/**
 * Input Security Validator — deteksi XSS dan SQL Injection pada field publik.
 * Disdukcapil Kabupaten Toba
 */
(function (global) {
    'use strict';

    var XSS_PATTERNS = [
        /<script[\s\S]*?>[\s\S]*?<\/script>/gi,
        /<[^>]+on\w+\s*=\s*["'][^"']*["']/gi,
        /javascript\s*:/gi,
        /vbscript\s*:/gi,
        /<iframe[\s\S]*?>/gi,
        /<img[^>]+src\s*=\s*["']?\s*javascript:/gi,
        /expression\s*\(/gi,
        /data\s*:\s*text\/html/gi,
        /&#x?[0-9a-f]+;?/gi,
        /<object[\s\S]*?>/gi,
        /<embed[\s\S]*?>/gi,
        /<svg[\s\S]*?>/gi,
        /document\.(cookie|write|location)/gi,
        /window\.(location|open|alert|eval)/gi,
        /eval\s*\(/gi,
        /alert\s*\(/gi,
        /prompt\s*\(/gi,
        /confirm\s*\(/gi,
        /\bXST\b/gi
    ];

    var SQLI_PATTERNS = [
        /(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION)\s/gi,
        /--\s*$/gm,
        /;\s*(DROP|DELETE|UPDATE|INSERT)/gi,
        /'\s*(OR|AND)\s*'?\d+'\s*=\s*'\d+/gi,
        /\bOR\b\s+\d+\s*=\s*\d+/gi,
        /'\s*(OR|AND)\s+\d+\s*=\s*\d+/gi,
        /SLEEP\s*\(\s*\d+\s*\)/gi,
        /BENCHMARK\s*\(/gi,
        /LOAD_FILE\s*\(/gi,
        /INTO\s+(OUTFILE|DUMPFILE)/gi,
        /INFORMATION_SCHEMA/gi,
        /CHAR\s*\(\s*\d+/gi,
        /0x[0-9a-f]{2,}/gi,
        /CAST\s*\(.*AS\s+(CHAR|INT)/gi,
        /CONVERT\s*\(/gi,
        /\bEXEC\b/gi,
        /xp_cmdshell/gi,
        /WAITFOR\s+DELAY/gi
    ];

    function isEmpty(value) {
        return value == null || String(value).trim() === '';
    }

    function detectXSS(value) {
        if (isEmpty(value)) {
            return { safe: true, type: null };
        }
        var str = String(value);
        for (var i = 0; i < XSS_PATTERNS.length; i++) {
            if (XSS_PATTERNS[i].test(str)) {
                XSS_PATTERNS[i].lastIndex = 0;
                return { safe: false, type: 'xss' };
            }
            XSS_PATTERNS[i].lastIndex = 0;
        }
        return { safe: true, type: null };
    }

    function detectSQLi(value) {
        if (isEmpty(value)) {
            return { safe: true, type: null };
        }
        var str = String(value);
        for (var i = 0; i < SQLI_PATTERNS.length; i++) {
            if (SQLI_PATTERNS[i].test(str)) {
                SQLI_PATTERNS[i].lastIndex = 0;
                return { safe: false, type: 'sqli' };
            }
            SQLI_PATTERNS[i].lastIndex = 0;
        }
        return { safe: true, type: null };
    }

    global.InputSecurityValidator = {
        detectXSS: detectXSS,
        detectSQLi: detectSQLi,
        XSS_PATTERNS: XSS_PATTERNS,
        SQLI_PATTERNS: SQLI_PATTERNS
    };
})(window);

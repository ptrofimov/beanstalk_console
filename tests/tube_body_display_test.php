<?php

require_once dirname(__FILE__) . '/../src/Settings.php';
require_once dirname(__FILE__) . '/../src/TubeBodyDisplayStorage.php';
require_once dirname(__FILE__) . '/../src/TubeBodyDisplaySettings.php';
require_once dirname(__FILE__) . '/../src/JobBodyFormatter.php';

$base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'beanstalk-console-tube-body-display-' . uniqid('', true);
mkdir($base);
$file = $base . DIRECTORY_SEPARATOR . 'tube-body-display.json';

$GLOBALS['config'] = array(
    'storage' => $base . DIRECTORY_SEPARATOR . 'storage.json',
    'settings' => array(
        'enableBase64Decode' => false,
        'enableUnserialization' => false,
        'enableJsonDecode' => true,
    ),
    'tubeBodyDisplay' => array(
        'storage' => $file,
    ),
);
$_COOKIE = array();

function assertTubeBodySame($expected, $actual, $message) {
    if ($expected !== $actual) {
        throw new Exception($message . ' Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

try {
    $storage = new TubeBodyDisplayStorage($GLOBALS['config']);
    $resolver = new TubeBodyDisplaySettings(new Settings(), $storage);
    $formatter = new JobBodyFormatter();

    $global = $resolver->getEffectiveSettings('server-a', 'json-tube');
    assertTubeBodySame(false, $global['enableBase64Decode'], 'Global base64 default should be used without override');
    assertTubeBodySame(false, $global['enableUnserialization'], 'Global unserialize default should be used without override');
    assertTubeBodySame(true, $global['enableJsonDecode'], 'Global JSON default should be used without override');
    assertTubeBodySame('global', $global['source'], 'Missing override should report global source');

    $resolver->saveOverride('server-a', 'wrapped-tube', array(
        'enableBase64Decode' => true,
        'enableUnserialization' => true,
        'enableJsonDecode' => false,
    ));

    $override = $resolver->getEffectiveSettings('server-a', 'wrapped-tube');
    assertTubeBodySame(true, $override['enableBase64Decode'], 'Tube override should enable base64');
    assertTubeBodySame(true, $override['enableUnserialization'], 'Tube override should enable unserialize');
    assertTubeBodySame(false, $override['enableJsonDecode'], 'Tube override should disable JSON');
    assertTubeBodySame('tube', $override['source'], 'Override should report tube source');

    $serialized = serialize(array('cmd' => 'httpCallGeneric'));
    $display = $formatter->formatForDisplay(base64_encode($serialized), $override, false);
    assertTubeBodySame('php', $display['content_type'], 'Base64-wrapped serialized bodies should render as PHP');
    if (strpos($display['body'], 'httpCallGeneric') === false) {
        throw new Exception('Serialized body display should include decoded value');
    }

    $resolver->saveOverride('server-a', 'json-wrapped-tube', array(
        'enableBase64Decode' => true,
        'enableUnserialization' => false,
        'enableJsonDecode' => true,
    ));
    $jsonOverride = $resolver->getEffectiveSettings('server-a', 'json-wrapped-tube');
    $jsonDisplay = $formatter->formatForDisplay(base64_encode('{"ok":true}'), $jsonOverride, false);
    assertTubeBodySame('json', $jsonDisplay['content_type'], 'Base64-wrapped JSON bodies should render as JSON');
    if (strpos($jsonDisplay['body'], '"ok": true') === false) {
        throw new Exception('JSON body display should be pretty printed after base64 decode');
    }

    $resolver->deleteOverride('server-a', 'wrapped-tube');
    $cleared = $resolver->getEffectiveSettings('server-a', 'wrapped-tube');
    assertTubeBodySame('global', $cleared['source'], 'Deleted override should fall back to global settings');

    echo "TubeBodyDisplay tests passed.\n";
} catch (Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

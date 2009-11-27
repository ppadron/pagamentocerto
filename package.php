<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$api_version     = '0.2.0';
$api_state       = 'alpha';

$release_version = '0.2.2';
$release_state   = 'alpha';
$release_notes   = "Second release based on PEAR-DEV comments.\n";

$description = "This package provides an interface to the Brazilian payment gateway PagamentoCerto.";

$package = new PEAR_PackageFileManager2();

$package->setOptions(
    array(
        'filelistgenerator'       => 'file',
        'simpleoutput'            => true,
        'baseinstalldir'          => '/',
        'packagedirectory'        => './',
        'dir_roles'               => array(
            'Payment'                         => 'php',
            'Payment/PagamentoCerto'          => 'php',
            'Payment/PagamentoCerto/examples' => 'php',
            'tests'                           => 'test'
        ),
        'ignore'                  => array(
            'package.php',
            '*.tgz'
        )
    )
);

$package->setPackage('Payment_PagamentoCerto');
$package->setSummary('PHP client to Brazilian payment gateway PagamentoCerto');
$package->setDescription($description);
$package->setChannel('pear.php.net');
$package->setPackageType('php');
$package->setLicense(
    'LGPL',
    'http://www.gnu.org/licenses/lgpl.html'
);

$package->setNotes($release_notes);
$package->setReleaseVersion($release_version);
$package->setReleaseStability($release_state);
$package->setAPIVersion($api_version);
$package->setAPIStability($api_state);

$package->addMaintainer(
    'lead',
    'ppadron',
    'Pedro Padron',
    'ppadron@php.net'
);

$package->setPhpDep('5.2');

$package->addPackageDepWithChannel(
    'required',
    'PEAR',
    'pear.php.net'
);

$package->addExtensionInstallCondition("soap");
$package->addExtensionInstallCondition("simplexml");

$package->setPearInstallerDep('1.7.0');
$package->generateContents();
$package->addRelease();

if (   isset($_GET['make'])
    || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')
) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}

?>


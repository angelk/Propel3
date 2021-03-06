<?php

/**
 * This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Propel\Tests\Generator\Behavior\I18n;

use Propel\Generator\Util\QuickBuilder;
use Propel\Tests\TestCase;

/**
 * Tests for I18nBehavior class object modifier
 *
 * @author François Zaninotto
 * @group skip
 */
class I18nBehaviorObjectBuilderModificationTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('\I18nBehaviorTest1')) {
            $schema = <<<EOF
<database name="i18n_behavior_test_1" activeRecord="true">
    <entity name="I18nBehaviorTest1">
        <field name="id" primaryKey="true" type="INTEGER" autoIncrement="true" />
        <field name="foo" type="INTEGER" />
        <field name="bar" type="VARCHAR" size="100" />
        <behavior name="i18n">
            <parameter name="i18n_fields" value="bar" />
        </behavior>
    </entity>
    <entity name="I18nBehaviorTest2">
        <field name="id" primaryKey="true" type="INTEGER" autoIncrement="true" />
        <field name="foo" type="INTEGER" />
        <field name="bar1" type="VARCHAR" size="100" />
        <field name="bar2" type="LONGVARCHAR" lazyLoad="true" />
        <field name="bar3" type="TIMESTAMP" />
        <field name="bar4" type="LONGVARCHAR" description="This is the Bar4 column" />
        <behavior name="i18n">
            <parameter name="i18n_fileds" value="bar1,bar2,bar3,bar4" />
            <parameter name="default_locale" value="fr_FR" />
            <parameter name="locale_alias" value="culture" />
        </behavior>
    </entity>
    <entity name="Movie">
        <field name="id" type="integer" required="true" primaryKey="true" autoincrement="true" />
        <field name="director" type="varchar" size="255" />
        <field name="title" type="varchar" primaryString="true" />
        <behavior name="i18n">
            <parameter name="i18n_fields" value="title" />
            <parameter name="locale_alias" value="culture" />
        </behavior>
    </entity>
    <entity name="Toy">
        <field name="id" type="integer" required="true" primaryKey="true" autoincrement="true" />
        <field name="ref" type="varchar" size="255" />
        <field name="name" type="varchar" size="255" />
        <behavior name="i18n">
            <parameter name="i18n_fields" value="name" />
            <parameter name="locale_alias" value="culture" />
        </behavior>
        <relation target="Movie" onDelete="cascade" />
    </entity>
    <entity name="I18nBehaviorTestLocalColumn">
        <field name="id" primaryKey="true" type="INTEGER" autoIncrement="true" />
        <field name="foo" type="INTEGER" />
        <field name="bar" type="VARCHAR" size="100" />
        <behavior name="i18n">
            <parameter name="i18n_fields" value="bar" />
            <parameter name="locale_field" value="my_lang" />
        </behavior>
    </entity>
</database>
EOF;
            QuickBuilder::buildSchema($schema);
        }
    }

    public function testPostDeleteEmulatesOnDeleteCascade()
    {
        \I18nBehaviorTest1Query::create()->deleteAll();
        \I18nBehaviorTest1I18nQuery::create()->deleteAll();
        $o = new \I18nBehaviorTest1();
        $o->setFoo(123);
        $o->setLocale('en_US');
        $o->setBar('hello');
        $o->setLocale('fr_FR');
        $o->setBar('bonjour');
        $o->save();

        $this->assertEquals(2, \I18nBehaviorTest1I18nQuery::create()->count());

        $refl = new \ReflectionObject($o);
        $refl->getProperty('i18nBehaviorTest1I18ns')->setAccessible(true);
        $o->i18nTest1I18ns = null;

        $o->delete();
        $this->assertEquals(0, \I18nBehaviorTest1I18nQuery::create()->count());
    }

    public function testGetTranslationReturnsTranslationObject()
    {
        $o = new \I18nBehaviorTest1();
        $translation = $o->getTranslation();
        $this->assertTrue($translation instanceof \I18nBehaviorTest1I18n);
    }

    public function testGetTranslationOnNewObjectReturnsNewTranslation()
    {
        $o = new \I18nBehaviorTest1();
        $translation = $o->getTranslation();
        $this->assertTrue($translation->isNew());
    }

    public function testGetTranslationOnPersistedObjectReturnsNewTranslation()
    {
        $o = new \I18nBehaviorTest1();
        $o->save();
        $translation = $o->getTranslation();
        $this->assertTrue($translation->isNew());
    }

    public function testGetTranslationOnPersistedObjectWithTranslationReturnsExistingTranslation()
    {
        $o = new \I18nBehaviorTest1();
        $translation = new \I18nBehaviorTest1I18n();
        $translation->setLocale('en_US');
        $o->addI18nBehaviorTest1I18n($translation);
        $o->save();

        $translation = $o->getTranslation();
        $this->assertFalse($translation->isNew());
    }

    public function testGetTranslationAcceptsALocaleParameter()
    {
        $o = new \I18nBehaviorTest1();
        $translation1 = new \I18nBehaviorTest1I18n();
        $translation1->setLocale('en_US');
        $o->addI18nBehaviorTest1I18n($translation1);
        $translation2 = new \I18nBehaviorTest1I18n();
        $translation2->setLocale('fr_FR');
        $o->addI18nBehaviorTest1I18n($translation2);
        $o->save();
        $this->assertEquals($translation1, $o->getTranslation('en_US'));
        $this->assertEquals($translation2, $o->getTranslation('fr_FR'));
    }

    public function testGetTranslationSetsTheLocaleOnTheTranslation()
    {
        $o = new \I18nBehaviorTest1();
        $o->save();
        $translation = $o->getTranslation();
        $this->assertEquals('en_US', $translation->getLocale());
        $o = new \I18nBehaviorTest2();
        $o->save();
        $translation = $o->getTranslation();
        $this->assertEquals('fr_FR', $translation->getLocale());
    }

    public function testGetTranslationUsesInternalCollectionIfAvailable()
    {
        $o = new \I18nBehaviorTest1();
        $translation1 = new \I18nBehaviorTest1I18n();
        $translation1->setLocale('en_US');
        $o->addI18nBehaviorTest1I18n($translation1);
        $translation2 = new \I18nBehaviorTest1I18n();
        $translation2->setLocale('fr_FR');
        $o->addI18nBehaviorTest1I18n($translation2);
        $translation = $o->getTranslation('en_US');
        $this->assertEquals($translation1, $translation);
    }

    public function testRemoveTranslation()
    {
        $o = new \I18nBehaviorTest1();
        $translation1 = new \I18nBehaviorTest1I18n();
        $translation1->setLocale('en_US');
        $o->addI18nBehaviorTest1I18n($translation1);
        $translation2 = new \I18nBehaviorTest1I18n();
        $translation2->setLocale('fr_FR');
        $translation2->setBar('bonjour');
        $o->addI18nBehaviorTest1I18n($translation2);
        $o->save();
        $this->assertEquals(2, $o->countI18nBehaviorTest1I18ns());
        $o->removeTranslation('fr_FR');
        $this->assertEquals(1, $o->countI18nBehaviorTest1I18ns());
        $translation = $o->getTranslation('fr_FR');
        $this->assertNotEquals($translation->getBar(), $translation2->getBar());
    }

    public function testLocaleSetterAndGetterExist()
    {
        $this->assertTrue(method_exists('\I18nBehaviorTest1', 'setLocale'));
        $this->assertTrue(method_exists('\I18nBehaviorTest1', 'getLocale'));
    }

    public function testGetLocaleReturnsDefaultLocale()
    {
        $o = new \I18nBehaviorTest1();
        $this->assertEquals('en_US', $o->getLocale());
        $o = new \I18nBehaviorTest2();
        $this->assertEquals('fr_FR', $o->getLocale());
    }

    public function testSetLocale()
    {
        $o = new \I18nBehaviorTest1();
        $o->setLocale('fr_FR');
        $this->assertEquals('fr_FR', $o->getLocale());
    }

    public function testSetLocaleUsesDefaultLocale()
    {
        $o = new \I18nBehaviorTest1();
        $o->setLocale('fr_FR');
        $o->setLocale();
        $this->assertEquals('en_US', $o->getLocale());
    }

    public function testLocaleSetterAndGetterAliasesExist()
    {
        $this->assertTrue(method_exists('\I18nBehaviorTest2', 'setCulture'));
        $this->assertTrue(method_exists('\I18nBehaviorTest2', 'getCulture'));
    }

    public function testGetLocaleAliasReturnsDefaultLocale()
    {
        $o = new \I18nBehaviorTest2();
        $this->assertEquals('fr_FR', $o->getCulture());
    }

    public function testSetLocaleAlias()
    {
        $o = new \I18nBehaviorTest2();
        $o->setCulture('en_US');
        $this->assertEquals('en_US', $o->getCulture());
    }

    public function testGetCurrentTranslationUsesDefaultLocale()
    {
        $o = new \I18nBehaviorTest1();
        $t = $o->getCurrentTranslation();
        $this->assertEquals('en_US', $t->getLocale());
        $o = new \I18nBehaviorTest2();
        $t = $o->getCurrentTranslation();
        $this->assertEquals('fr_FR', $t->getLocale());
    }

    public function testGetCurrentTranslationUsesCurrentLocale()
    {
        $o = new \I18nBehaviorTest1();
        $o->setLocale('fr_FR');
        $this->assertEquals('fr_FR', $o->getCurrentTranslation()->getLocale());
        $o->setLocale('pt_PT');
        $this->assertEquals('pt_PT', $o->getCurrentTranslation()->getLocale());
    }

    public function testI18nColumnGetterUsesCurrentTranslation()
    {
        $o = new \I18nBehaviorTest1();
        $t1 = $o->getCurrentTranslation();
        $t1->setBar('hello');
        $o->setLocale('fr_FR');
        $t2 = $o->getCurrentTranslation();
        $t2->setBar('bonjour');
        //$o->save();
        $o->setLocale('en_US');
        $this->assertEquals('hello', $o->getBar());
        $o->setLocale('fr_FR');
        $this->assertEquals('bonjour', $o->getBar());
    }

    public function testI18nColumnSetterUsesCurrentTranslation()
    {
        $o = new \I18nBehaviorTest1();
        $o->setBar('hello');
        $o->setLocale('fr_FR');
        $o->setBar('bonjour');
        $o->setLocale('en_US');
        $this->assertEquals('hello', $o->getBar());
        $o->setLocale('fr_FR');
        $this->assertEquals('bonjour', $o->getBar());
    }

    public function testTranslationsArePersisted()
    {
        $o = new \I18nBehaviorTest1();
        $o->save();
        $count = \I18nBehaviorTest1I18nQuery::create()
            ->filterById($o->getId())
            ->count();
        $this->assertEquals(0, $count);
        $o->setBar('hello');
        $o->setLocale('fr_FR');
        $o->setBar('bonjour');
        $o->save();
        $count = \I18nBehaviorTest1I18nQuery::create()
            ->filterById($o->getId())
            ->count();
        $this->assertEquals(1, $count);
    }

    public function testI18nWithRelations()
    {
        \MovieQuery::create()->deleteAll();
        $count = \MovieQuery::create()->count();
        $this->assertEquals(0, $count, 'No movie before the test');
        \ToyQuery::create()->deleteAll();
        $count = \ToyQuery::create()->count();
        $this->assertEquals(0, $count, 'No toy before the test');
        \MovieI18nQuery::create()->deleteAll();
        $count = \MovieI18nQuery::create()->count();
        $this->assertEquals(0, $count, 'No i18n movies before the test');

        $m = new \Movie();
        $m->setLocale('en');
        $m->setTitle('V For Vendetta');
        $m->setLocale('fr');
        $m->setTitle('V Pour Vendetta');

        $m->setLocale('en');
        $this->assertEquals('V For Vendetta', $m->getTitle());
        $m->setLocale('fr');
        $this->assertEquals('V Pour Vendetta', $m->getTitle());

        $t = new \Toy();
        $t->setMovie($m);
        $t->save();

        $count = \MovieQuery::create()->count();
        $this->assertEquals(1, $count, '1 movie');
        $count = \ToyQuery::create()->count();
        $this->assertEquals(1, $count, '1 toy');
        $count = \MovieI18nQuery::create()->count();

        $this->assertEquals(2, $count, '2 i18n movies');
        $count = \ToyI18nQuery::create()->count();
        $this->assertEquals(0, $count, '0 i18n toys');
    }

    public function testI18nWithRelations2()
    {
        \MovieI18nQuery::create()->deleteAll();
        \MovieQuery::create()->deleteAll();
        $count = \MovieQuery::create()->count();
        $this->assertEquals(0, $count, 'No movie before the test');
        \ToyQuery::create()->deleteAll();
        $count = \ToyQuery::create()->count();
        $this->assertEquals(0, $count, 'No toy before the test');
        \ToyI18nQuery::create()->deleteAll();
        $count = \ToyI18nQuery::create()->count();
        $this->assertEquals(0, $count, 'No i18n toys before the test');
        \MovieI18nQuery::create()->deleteAll();
        $count = \MovieI18nQuery::create()->count();
        $this->assertEquals(0, $count, 'No i18n movies before the test');

        $t = new \Toy();
        $t->setLocale('en');
        $t->setName('My Name');
        $t->setLocale('fr');
        $t->setName('Mon Nom');

        $t->setLocale('en');
        $this->assertEquals('My Name', $t->getName());
        $t->setLocale('fr');
        $this->assertEquals('Mon Nom', $t->getName());

        $m = new \Movie();
        $m->addToy($t);
        $m->save();

        $count = \MovieQuery::create()->count();
        $this->assertEquals(1, $count, '1 movie');

        $count = \ToyQuery::create()->count();
        $this->assertEquals(1, $count, '1 toy');

        $count = \ToyI18nQuery::create()->count();
        $this->assertEquals(2, $count, '2 i18n toys');

        $count = \MovieI18nQuery::create()->count();
        $this->assertEquals(0, $count, '0 i18n movies');
    }

    public function testUseLocalColumnParameter()
    {
        $o = new \I18nBehaviorTestLocalColumn();
        $translation1 = new \I18nBehaviorTestLocalColumnI18n();
        $translation1->setMyLang('en_US');
        $o->addI18nBehaviorTestLocalColumnI18n($translation1);
        $translation2 = new \I18nBehaviorTestLocalColumnI18n();
        $translation2->setMyLang('fr_FR');
        $o->addI18nBehaviorTestLocalColumnI18n($translation2);
        $o->save();
        $this->assertEquals($translation1, $o->getTranslation('en_US'));
        $this->assertEquals($translation2, $o->getTranslation('fr_FR'));
    }
}


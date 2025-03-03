<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Tests\Schema\Conveyor;

use Cycle\Annotated\Entities;
use Yiisoft\Yii\Cycle\Exception\EmptyEntityPathsException;
use Yiisoft\Yii\Cycle\Schema\Conveyor\CompositeSchemaConveyor;
use Yiisoft\Yii\Cycle\Schema\Conveyor\MetadataSchemaConveyor;
use Cycle\Schema\Generator\GenerateTypecast;
use Cycle\Annotated\MergeIndexes;
use Cycle\Schema\Generator\RenderRelations;
use Cycle\Schema\Generator\RenderTables;
use Cycle\Schema\Generator\ValidateEntities;
use Cycle\Schema\Generator\GenerateRelations;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\Embeddings;
use Cycle\Schema\Generator\ResetTables;
use Cycle\Schema\Generator\SyncTables;

class MetadataSchemaConveyorTest extends BaseConveyorTest
{
    final public function testGetTableNamingDefault(): void
    {
        $conveyor = $this->createConveyor();

        $this->assertSame(Entities::TABLE_NAMING_SINGULAR, $conveyor->getTableNaming());
    }

    final public function tableNamingProvider(): array
    {
        return [
            [Entities::TABLE_NAMING_PLURAL],
            [Entities::TABLE_NAMING_SINGULAR],
            [Entities::TABLE_NAMING_NONE],
        ];
    }

    /**
     * @dataProvider tableNamingProvider
     */
    final public function testSetTableNaming(int $naming): void
    {
        $conveyor = $this->createConveyor();

        $conveyor->setTableNaming($naming);

        $this->assertSame($naming, $conveyor->getTableNaming());
    }

    final public function testDefaultGeneratorsOrder(): void
    {
        $conveyor = $this->createConveyor();

        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            ResetTables::class,
            Embeddings::class,
            Entities::class,
            MergeColumns::class,
            GenerateRelations::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            MergeIndexes::class,
            GenerateTypecast::class,
        ], $generators);
    }

    final public function testAddCustomGenerator(): void
    {
        $conveyor = $this->createConveyor();
        $conveyor->addGenerator($conveyor::STAGE_USERLAND, \Cycle\Schema\Generator\SyncTables::class);

        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            ResetTables::class,
            Embeddings::class,
            Entities::class,
            MergeColumns::class,
            GenerateRelations::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            MergeIndexes::class,
            SyncTables::class,
            GenerateTypecast::class,
        ], $generators);
    }

    final public function testEmptyEntityPaths(): void
    {
        $conveyor = $this->createConveyor([]);

        $this->expectException(EmptyEntityPathsException::class);

        $conveyor->getGenerators();
    }

    final public function testAnnotatedGeneratorsAddedOnlyOnce(): void
    {
        $conveyor = $this->createConveyor();

        $conveyor->getGenerators();
        $conveyor->getGenerators();
        $generators = $this->getGeneratorClassList($conveyor);

        $this->assertSame([
            ResetTables::class,
            Embeddings::class,
            Entities::class,
            MergeColumns::class,
            GenerateRelations::class,
            ValidateEntities::class,
            RenderTables::class,
            RenderRelations::class,
            MergeIndexes::class,
            GenerateTypecast::class,
        ], $generators);
    }

    /**
     * @param string[] $entityPaths
     */
    public function createConveyor(array $entityPaths = ['@test-dir']): MetadataSchemaConveyor
    {
        $conveyor = new CompositeSchemaConveyor($this->prepareContainer());
        $conveyor->addEntityPaths($entityPaths);

        return $conveyor;
    }
}

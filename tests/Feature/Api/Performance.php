<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\WithFaker;
use ProcessMaker\Models\Group;
use ProcessMaker\Models\Process;
use Tests\Feature\Shared\ResourceAssertionsTrait;
use Tests\TestCase;
use Tests\Feature\Shared\RequestHelper;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;
use ReflectionObject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use ProcessMaker\Models\User;
use ProcessMaker\Models\Comment;

/**
 * Tests routes related to processes / CRUD related methods
 *
 * @group process_tests
 */
class ProcessTest extends TestCase
{
    use WithFaker;
    use RequestHelper;
    use ResourceAssertionsTrait;

    // Speed of model Group creation (records/unit_time):
    // u=71.48 σ=22.16 => Min Speed of distribution = 27.16
    // Maximum allowed payload per creation: 2 times the creation
    const MIN_SPEED = 27 / (1 + 2 * 71 / 27);

    private $exceptions = [
        // Hi payload because of password hash
        //'ProcessMaker\Models\User' => 27*6/100,
    ];

    /**
     * List the factories
     *
     * @return array
     */
    public function FactoryListProvider()
    {
        $factories = app(EloquentFactory::class);
        $reflection = new ReflectionObject($factories);
        $property = $reflection->getProperty('definitions');
        $property->setAccessible(true);
        $definitions = $property->getValue($factories);

        $baseTime = $this->calculateUnitTime();

        $models = [];
        foreach ($definitions as $model => $definition) {
            //if ($model==='ProcessMaker\Models\Notification') {
            $models[] = [$model, $baseTime];
            //}
        }
        return $models;
    }

    /**
     * Time unit base for the performce tests
     *
     * @param integer $times
     *
     * @return float
     */
    private function calculateUnitTime($times = 100)
    {
        $model = Group::class;
        $t = microtime(true);
        factory($model, $times)->create();
        $baseTime = microtime(true) - $t;
        $model::getQuery()->delete();
        return $baseTime;
    }

    /**
     *
     *
     * @param [type] $model
     * @param [type] $baseCount
     * @param [type] $baseTime
     *
     * @dataProvider CaseProvider
     */
    public function testFactories($model, $baseTime)
    {
        $baseCount = $this->getTotalRecords();
        $t = microtime(true);
        factory($model)->create();
        $time = microtime(true) - $t;
        $count = $this->getTotalRecords();
        $speed = ($count - $baseCount) / ($time / $baseTime);

        $minSpeed = isset($this->exceptions[$model]) ? $this->exceptions[$model] : self::MIN_SPEED;
        error_log('[' . $speed / $minSpeed . ']');
        $this->assertGreaterThanOrEqual($minSpeed, $speed);
    }

    /**
     * Get total count of records in the databases
     *
     * @return int
     */
    private function getTotalRecords()
    {
        $tables = [];
        foreach (config('database.connections') as $name => $config) {
            $connection = DB::connection($name);
            $list = $connection->getDoctrineSchemaManager()->listTableNames();
            foreach ($list as $table) {
                if (!isset($tables[$table])) {
                    $tables[$table] = $connection->table($table)->count();
                }
            }
        }
        return array_sum($tables);
    }

    private $endpoints = [
        ['l5-swagger.oauth2_callback', []],
        ['horizon.stats.index', []],
        ['horizon.workload.index', []],
        ['horizon.masters.index', []],
        ['horizon.monitoring.index', []],
        ['horizon.jobs-metrics.index', []],
        ['horizon.queues-metrics.index', []],
        ['horizon.recent-jobs.index', []],
        ['horizon.failed-jobs.index', []],
        ['passport.authorizations.authorize', []],
        ['passport.tokens.index', []],
        ['passport.clients.index', []],
        ['api.users.index', []],
        ['api.groups.index', []],
        ['api.group_members.index', []],
        ['api.group_members_available.show', []],
        ['api.user_members_available.show', []],
        ['api.environment_variables.index', []],
        ['api.screens.index', []],
        ['api.screen_categories.index', []],
        ['api.scripts.index', []],
        ['api.processes.index', []],
        ['api.processes.start', []],
        ['api.process_categories.index', []],
        ['api.tasks.index', []],
        ['api.requests.index', []],
        ['api.files.index', []],
        ['api.notifications.index', []],
        ['api.task_assignments.index', []],
        ['api.comments.index', []],
        ['groups.index', []],
        ['users.index', []],
        ['auth-clients.index', []],
        ['customize-ui.edit', []],
        ['admin.index', []],
        ['environment-variables.index', []],
        ['screens.index', []],
        ['screens.import', []],
        ['scripts.index', []],
        ['categories.index', []],
        ['processes.index', []],
        ['processes.import', []],
        ['processes.create', []],
        ['about.index', []],
        ['profile.edit', []],
        ['home', []],
        ['requests.index', []],
        ['tasks.index', []],
        ['notifications.index', []],
        ['login', []],
        ['logout', []],
        ['password.request', []],
        ['password-success', []],
        ['error.unavailable', []],
    ];

    public function RoutesListProvider()
    {
        try {
            $this->user = factory(User::class)->create(['is_administrator' => true]);

            factory(Comment::class, 200)->create();
        } catch (\Throwable $t) {
            dump($t->getMessage());
        }
        return $this->endpoints;
    }

    /**
     * Test routes speed
     *
     * @dataProvider RoutesListProvider
     */
    public function testRoutesSpeed($route, $params)
    {
        $this->actingAs($this->user);
        $this->withoutExceptionHandling();

        $baseTime = $this->calculateUnitTime();

        // Test endpoint
        $path = route($route, $params);
        $fn = (substr($route, 0, 4) === 'api.') ? 'apiCall' : 'webCall';
        $times = 10;
        $t = microtime(true);
        for ($i = 0;$i < $times;$i++) {
            $this->$fn('GET', $path);
        }
        $time = microtime(true) - $t;

        $requestsPerSecond = round($times / $time * 10) / 10;
        $speed = $times / ($time / $baseTime);
        echo "[$route = $speed]\n";
        $this->assertGreaterThanOrEqual(3, $speed, "Slow route response [$route]\n             Speed ~ $requestsPerSecond [reqs/sec]");
    }
}

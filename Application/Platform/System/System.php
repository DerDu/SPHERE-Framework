<?php
namespace SPHERE\Application\Platform\System;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\System\Anonymous\Anonymous;
use SPHERE\Application\Platform\System\BasicData\BasicData;
use SPHERE\Application\Platform\System\Cache\Cache;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\Session\Session;
use SPHERE\Application\Platform\System\Test\Test;
use SPHERE\Application\Platform\System\DataMaintenance\DataMaintenance;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class System
 *
 * @package SPHERE\Application\Platform\System
 */
class System implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Protocol::registerModule();
        Database::registerModule();
        Cache::registerModule();
//        Archive::registerModule();
        Test::registerModule();
        Session::registerModule();
        DataMaintenance::registerModule();
        Anonymous::registerModule();
        BasicData::registerModule();
        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('System'), new Link\Icon(new Cog()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'System');

        $table = '<div class="p-2">Zustand: ' . gethostname() . '</div><br/>';

        $table .= '<div class="row">';
        $table .= '<div class="col-4">';
        try {

            $core_nums = trim(shell_exec("grep -P '^physical id' /proc/cpuinfo|wc -l"));
            $loads = sys_getloadavg();
            $table .= '<h3>Prozessor<small class="text-muted">: ' . $core_nums . ' Kerne</small></h3>';

            $load1 = $loads[0] * 100 / $core_nums;
            $load5 = $loads[1] * 100 / $core_nums;
            $load15 = $loads[2] * 100 / $core_nums;

            $table .= '<small>letzte Minute</small>';
            $pb = new ProgressBar(0, $load1,100-$load1, 10 );
            $pb->setColor(ProgressBar::BAR_COLOR_DANGER, ProgressBar::BAR_COLOR_WARNING, ProgressBar::BAR_COLOR_SUCCESS);
            $table .= $pb;
            $table .= '<small>letzten 5 Minuten</small>';
            $pb = new ProgressBar(0, $load5,100-$load5, 10);
            $pb->setColor(ProgressBar::BAR_COLOR_DANGER, ProgressBar::BAR_COLOR_WARNING, ProgressBar::BAR_COLOR_SUCCESS);
            $table .= $pb;
            $table .= '<small>letzten 15 Minuten</small>';
            $pb = new ProgressBar(0, $load15,100-$load15, 10);
            $pb->setColor(ProgressBar::BAR_COLOR_DANGER, ProgressBar::BAR_COLOR_WARNING, ProgressBar::BAR_COLOR_SUCCESS);
            $table .= $pb;
            $table .= '<br/>';

        } catch (\Throwable $exception) {
            $table .= $exception->getMessage();
        }

        $table .= '</div>';
        $table .= '<div class="col-4">';

        try {

            $free = shell_exec('free');
            $free = (string)trim($free);
            $free_arr = explode("\n", $free);
            if (count($free_arr) > 1) {
                $mem = explode(" ", $free_arr[1]);
                $mem = array_filter($mem);
                $mem = array_merge($mem);

                preg_match_all('!\b[^\s]+\b!is', $free_arr[0], $keys);
                $values = array_slice($mem, 1);
                $info = array_combine($keys[0], $values);

                $Total = $info['total'];

                $Used = 100 / $Total * $info['used'];
                $Buff = 100 / $Total * $info['buffers'];
                $Free = 100 / $Total * $info['free'];

                $table .= '<h3>Speicher<small class="text-muted">: ' . $this->formatBytes($mem[1] * 1024) . '</small></h3>';
                $table .= '<small>Gesamt: ' . number_format((float)$info['total'], 0, ',', '.') . '</small>';
                $pb = new ProgressBar($Used,$Buff, 100 - $Used - $Buff, 10);
                $pb->setColor(ProgressBar::BAR_COLOR_DANGER, ProgressBar::BAR_COLOR_WARNING, ProgressBar::BAR_COLOR_SUCCESS);
                $table .= $pb;

                $table .= '<small>Frei: ' . number_format((float)$info['free'], 0, ',', '.') . '</small>';
                $table .= '<br/>';
                $table .= '<small>Buffer: ' . number_format((float)$info['buffers'], 0, ',', '.') . '</small>';
                $table .= '<br/>';
            }

        } catch (\Throwable $exception) {
            $table .= $exception->getMessage();
        }

        $table .= '</div>';
        $table .= '<div class="col-4">';

        try {
            $Value = 100 / disk_total_space(__DIR__) * disk_free_space(__DIR__);
            $Free = $Value;
            $Used = 100 - $Value;
            $table .= '<h3>Festplatte<small class="text-muted">: ' . $this->formatBytes(disk_total_space(__DIR__)) . '</small></h3>';
            $table .= '<small>Gesamt: ' . number_format(disk_total_space(__DIR__), 0, ',', '.') . '</small>';

            $pb = new ProgressBar($Used, 0,$Free, 10);
            $pb->setColor(ProgressBar::BAR_COLOR_DANGER, ProgressBar::BAR_COLOR_WARNING, ProgressBar::BAR_COLOR_SUCCESS);
            $table .= $pb;

            $table .= '<small>Frei: ' . number_format(disk_free_space(__DIR__), 0, ',', '.') . '</small>';
            $table .= '<br/>';
        } catch (\Throwable $exception) {
            $table .= $exception->getMessage();
        }

        $table .= '</div>';
        $table .= '</div>';

        $Stage->setContent(
            $table
        );

        return $Stage;
    }

    /**
     * Formats bytes into a human readable string if $this->useFormatting is true, otherwise return $bytes as is
     *
     * @param  int $bytes
     * @return string|int Formatted string if $this->useFormatting is true, otherwise return $bytes as is
     */
    private function formatBytes($bytes)
    {
        $bytes = (int)$bytes;
        if ($bytes > 1024 * 1024 * 1024) {
            return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
        } elseif ($bytes > 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2) . ' MB';
        } elseif ($bytes > 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}

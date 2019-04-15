<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\Tour\Engagement\ChallengeAddedAnnotation;
use Piwik\Plugins\Tour\Engagement\ChallengeAddedUser;
use Piwik\Plugins\Tour\Engagement\ChallengeCreatedGoal;
use Piwik\Plugins\Tour\Engagement\ChallengeViewRowEvolution;
use Piwik\Plugins\Tour\Engagement\ChallengeViewVisitorProfile;
use Piwik\Plugins\Tour\Engagement\ChallengeViewVisitsLog;

class Tour extends \Piwik\Plugin
{

    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Dashboard.changeDefaultDashboardLayout' => 'changeDefaultDashboardLayout',
            'API.Annotations.add.end' => 'onAnnotationAdded',
            'API.Goals.addGoal.end' => 'onGoalAdded',
            'API.UsersManager.addUser' => 'onUserAdded',
            'Controller.CoreHome.getRowEvolutionPopover' => 'onViewRowEvolution',
            'Controller.Live.getLastVisitsDetails' => 'onViewVisitorLog',
            'Controller.Live.getVisitorProfilePopup' => 'onViewVisitorProfile',
        );
    }

    private function setSimpleChallengeCompleted($className)
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $annotation = StaticContainer::get($className);
            $annotation->setCompleted();
        }
    }

    public function onViewRowEvolution()
    {
        $this->setSimpleChallengeCompleted(ChallengeViewRowEvolution::class);
    }

    public function onViewVisitorLog()
    {
        $this->setSimpleChallengeCompleted(ChallengeViewVisitsLog::class);
    }

    public function onViewVisitorProfile()
    {
        $this->setSimpleChallengeCompleted(ChallengeViewVisitorProfile::class);
    }

    public function onAnnotationAdded($response)
    {
        if (Piwik::hasUserSuperUserAccess() && !empty($response)) {
            $annotation = new ChallengeAddedAnnotation();
            $annotation->setCompleted();
        }
    }

    public function onGoalAdded($response)
    {
        if (Piwik::hasUserSuperUserAccess() && !empty($response)) {
            $annotation = new ChallengeCreatedGoal();
            $annotation->setCompleted();
        }
    }

    public function onUserAdded($response)
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $annotation = new ChallengeAddedUser();
            $annotation->setCompleted();
        }
    }

    public function changeDefaultDashboardLayout(&$defaultLayout)
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $defaultLayout = json_decode($defaultLayout);
            $engagementWidget = array('uniqueId' => 'widgetTourgetEngagement', 'parameters' => array('module' => 'Tour', 'action' => 'getEngagement'));
            array_unshift($defaultLayout[0], $engagementWidget);

            $defaultLayout = json_encode($defaultLayout);
        }
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Tour/stylesheets/engagement.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Tour/javascripts/engagement.js";
    }
}
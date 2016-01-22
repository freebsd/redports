<?php

namespace Redports\Master;

/**
 * Build job for an individual port on an individual jail.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
class Job
{
    protected $_db;
    protected $_jobid = null;
    protected $_data = null;
    protected $_queues = array('preparequeue', 'waitqueue', 'runqueue', 'archivequeue');

    public function __construct($jobid = null)
    {
        $this->_db = Config::getDatabaseHandle();

        $this->_jobid = $jobid;
        $this->_load();
    }

    public function exists()
    {
        if ($this->_jobid == null) {
            return false;
        }

        return $this->_db->exists('jobs:'.$this->_jobid);
    }

    public function _load()
    {
        if ($this->exists()) {
            $this->_data = json_decode($this->_db->get('jobs:'.$this->_jobid), true);
            return true;
        }

        return false;
    }

    public function save()
    {
        if ($this->_jobid == null) {
            $this->_jobid = $this->_db->incr('sequ:jobs');
            $this->_data['jobid'] = $this->_jobid;

            $this->_db->sAdd('alljobs', $this->_jobid);
            $this->_db->sAdd('repojobs:'.$this->getRepository(), $this->_jobid);
            $this->_db->zAdd($this->getFullQueue(), $this->getPriority(), $this->_jobid);
        }

        return $this->_db->set('jobs:'.$this->_jobid, json_encode($this->_data));
    }

    public function getJobId()
    {
        return $this->_jobid;
    }

    public function getFullQueue()
    {
        return $this->_data['queue'].':'.$this->_data['jail'];
    }

    public function getQueue()
    {
        return $this->_data['queue'];
    }

    public function getRepository()
    {
        return $this->_data['repository']['url'];
    }

    public function getPriority()
    {
        return $this->_db->zScore($this->getFullQueue(), $this->_jobid);
    }

    public function incPriority($inc)
    {
        return $this->_db->zIncrBy($this->getFullQueue(), $inc, $this->_jobid);
    }

    public function setPriority($newprio)
    {
        return $this->incPriority($newprio - $this->getPriority());
    }

    public function getJobData()
    {
        return $this->_data;
    }

    public function setJobData($data)
    {
        if (!isset($data['queue'])) {
            $data['queue'] = $this->_queues[0];
        }

        if (!isset($data['priority'])) {
            $data['priority'] = 50;
        }

        if (!isset($data['jail'])) {
            return false;
        }

        if (!isset($data['repository'])) {
            return false;
        }

        if (!isset($data['port'])) {
            return false;
        }

        $data['jobid'] = $this->getJobId();

        $this->_data = $data;

        return true;
    }

    public function get($field)
    {
        if (isset($this->_data[$field])) {
            return $this->_data[$field];
        }

        return;
    }

    public function set($field, $value)
    {
        if ($field == 'jobid') {
            return false;
        }

        $this->_data[$field] = $value;

        return true;
    }

    public function del($field)
    {
        if ($field == 'jobid') {
            return false;
        }

        if (!isset($this->_data[$field])) {
            return false;
        }

        unset($this->_data[$field]);

        return true;
    }

    public function moveToQueue($queue)
    {
        if (!in_array($queue, $this->_queues)) {
            trigger_error('Queuename is invalid', E_USER_ERROR);

            return false;
        }

        $score = $this->getPriority();

        if ($this->_db->zRem($this->getFullQueue(), $this->_jobid) !== true) {
            return false;
        }

        $this->_data['queue'] = $queue;

        if ($this->_db->zAdd($this->getFullQueue(), $score, $this->_jobid) !== true) {
            return false;
        }

        return $this->save();
    }
}

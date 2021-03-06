<?php

namespace SlmQueueDoctrine\Worker;

use Exception;
use SlmQueue\Job\JobInterface;
use SlmQueue\Queue\QueueInterface;
use SlmQueue\Worker\AbstractWorker;
use SlmQueueDoctrine\Queue\TableInterface;
use SlmQueueDoctrine\Job\Exception as JobException;

/**
 * Worker for Doctrine
 */
class Worker extends AbstractWorker
{
    /**
     * {@inheritDoc}
     */
    public function processJob(JobInterface $job, QueueInterface $queue)
    {
        if (!$queue instanceof TableInterface) {
            return;
        }

        try {
            $job->execute($queue);
            $queue->delete($job);
        } catch(JobException\ReleasableException $exception) {
            $queue->release($job, $exception->getOptions());
        } catch (JobException\BuryableException $exception) {
            $queue->bury($job, $exception->getOptions());
        } catch (Exception $exception) {
            $queue->bury($job, array('message' => $exception->getMessage(),
                                     'trace' => $exception->getTraceAsString()));
        }
    }
}

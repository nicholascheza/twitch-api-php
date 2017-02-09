<?php

namespace TwitchApi\Api;

use TwitchApi\Exceptions\InvalidDirectionException;
use TwitchApi\Exceptions\InvalidIdentifierException;
use TwitchApi\Exceptions\InvalidLimitException;
use TwitchApi\Exceptions\InvalidOffsetException;
use TwitchApi\Exceptions\InvalidTypeException;
use TwitchApi\Exceptions\TwitchApiException;
use TwitchApi\Exceptions\UnsupportedOptionException;

trait Channels
{
    /**
     * Get a user's own channel
     *
     * @param string $accessToken
     * @return array|json
     */
    public function getAuthenticatedChannel($accessToken)
    {
        return $this->get('channel', [], $accessToken);
    }

    /**
     * Get a channel
     *
     * @param string|int $channelIdentifier
     * @throws InvalidIdentifierException
     * @return array|json
     */
    public function getChannel($channelIdentifier)
    {
        if ($this->apiVersionIsGreaterThanV4() && !is_numeric($channelIdentifier)) {
            throw new InvalidIdentifierException('channel');
        }

        return $this->get(sprintf('channels/%s', $channelIdentifier));
    }

    /**
     * Update a user's channel
     *
     * @param string|int $channelIdentifier
     * @param string     $accessToken
     * @param string     $status
     * @param string     $game
     * @param int        $delay
     * @param bool       $channelFeedEnabled
     * @throws InvalidIdentifierException
     * @throws InvalidTypeException
     * @throws TwitchApiException
     * @return array|json
     */
    public function updateChannel($channelIdentifier, $accessToken, $status = null, $game = null, $delay = null, $channelFeedEnabled = null)
    {
        $params = [];
        $params['channel'] = [];

        if ($this->apiVersionIsGreaterThanV4() && !is_numeric($channelIdentifier)) {
            throw new InvalidIdentifierException('channel');
        }

        if ($status) {
            if (!is_string($status)) {
                throw new InvalidTypeException('Status', 'string', gettype($status));
            }
            $params['channel']['status'] = $status;
        }

        if ($game) {
            if (!is_string($game)) {
                throw new InvalidTypeException('Game', 'string', gettype($game));
            }
            $params['channel']['game'] = $game;
        }

        if ($delay) {
            if (!is_numeric($delay)) {
                throw new InvalidTypeException('Delay', 'integer', gettype($delay));
            }
            $params['channel']['delay'] = intval($delay);
        }

        if ($channelFeedEnabled !== null) {
            if (!is_bool($channelFeedEnabled)) {
                throw new InvalidTypeException('ChannelFeedEnabled', 'boolean', gettype($channelFeedEnabled));
            }
            $params['channel']['channel_feed_enabled'] = $channelFeedEnabled;
        }

        if (empty($params['channels'])) {
            throw new TwitchApiException('At least one of the following parameters must be set: status, game, delay or channelFeedEnabled.');
        }

        return $this->put(sprintf('channels/%s', $channelIdentifier), $params, $accessToken);
    }

    /**
     * Get channel editors
     *
     * @param string|int $channelIdentifier
     * @param string     $accessToken
     * @throws InvalidIdentifierException
     * @return array|json
     */
    public function getChannelEditors($channelIdentifier, $accessToken)
    {
        if ($this->apiVersionIsGreaterThanV4() && !is_numeric($channelIdentifier)) {
            throw new InvalidIdentifierException('channel');
        }

        return $this->get(sprintf('channels/%s/editors', $channelIdentifier), [], $accessToken);
    }

    /**
     * Get channel followers
     *
     * @param string|int $channelIdentifier
     * @param int        $limit
     * @param int        $offset
     * @param string     $cursor
     * @param string     $direction
     * @throws InvalidIdentifierException
     * @throws InvalidLimitException
     * @throws InvalidOffsetException
     * @throws InvalidTypeException
     * @throws UnsupportedOptionException
     * @return array|json
     */
    public function getChannelFollowers($channelIdentifier, $limit = 25, $offset = 0, $cursor = null, $direction = 'desc')
    {
        if ($this->apiVersionIsGreaterThanV4() && !is_numeric($channelIdentifier)) {
            throw new InvalidIdentifierException('channel');
        }

        if (!$this->isValidLimit($limit)) {
            throw new InvalidLimitException();
        }

        if (!$this->isValidOffset($offset)) {
            throw new InvalidOffsetException();
        }

        if ($cursor && !is_string($cursor)) {
            throw new InvalidTypeException('Cursor', 'string', gettype($cursor));
        }

        if (!$this->isValidDirection($direction)) {
            throw new InvalidDirectionException();
        }

        return $this->get(sprintf('channels/%s/follows', $channelIdentifier), [
            'limit' => intval($limit),
            'offset' => intval($offset),
            'cursor' => $cursor,
            'direction' => $direction,
        ]);
    }

    /**
     * Get channel teams
     *
     * @param string|int $channelIdentifier
     * @throws InvalidIdentifierException
     * @return array|json
     */
    public function getChannelTeams($channelIdentifier)
    {
        if ($this->apiVersionIsGreaterThanV4() && !is_numeric($channelIdentifier)) {
            throw new InvalidIdentifierException('channel');
        }

        return $this->get(sprintf('channels/%s/teams', $channelIdentifier));
    }

    /**
     * Get channel subscribers
     *
     * @param string|int $channelIdentifier
     * @param string     $accessToken
     * @param int        $limit
     * @param int        $offset
     * @param string     $direction
     * @throws InvalidIdentifierException
     * @throws InvalidLimitException
     * @throws InvalidOffsetException
     * @throws InvalidDirectionException
     * @return array|json
     */
    public function getChannelSubscribers($channelIdentifier, $accessToken, $limit = 25, $offset = 0, $direction = 'desc')
    {
        if ($this->apiVersionIsGreaterThanV4() && !is_numeric($channelIdentifier)) {
            throw new InvalidIdentifierException('channel');
        }

        if (!$this->isValidLimit($limit)) {
            throw new InvalidLimitException();
        }

        if (!$this->isValidOffset($offset)) {
            throw new InvalidOffsetException();
        }

        if (!$this->isValidDirection($direction)) {
            throw new InvalidDirectionException();
        }

        $params = [
            'limit' => intval($limit),
            'offset' => intval($offset),
            'direction' => $direction,
        ];

        return $this->get(sprintf('channels/%s/subscriptions', $channelIdentifier), $params, $accessToken);
    }

    /**
     * Check a user's subscription to a channel
     *
     * @param string|int $channelIdentifier
     * @param string|int $userIdentifier
     * @param string     $accessToken
     * @throws InvalidIdentifierException
     * @return array|json
     */
    public function checkChannelSubscriptionByUser($channelIdentifier, $userIdentifier, $accessToken)
    {
        if ($this->apiVersionIsGreaterThanV4()) {
            if (!is_numeric($channelIdentifier)) {
                throw new InvalidIdentifierException('channel');
            }

            if (!is_numeric($userIdentifier)) {
                throw new InvalidIdentifierException('user');
            }
        }

        return $this->get(sprintf('channels/%s/subscriptions/%s', $channelIdentifier, $userIdentifier), [], $accessToken);
    }
}

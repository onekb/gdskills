<?php

namespace Onekb\Gdskills;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Guzzle
{
    /** @var string */
    protected static $userToken;

    /** @var Client */
    protected static $client;

    /** @var array */
    protected static $records;


    public static function login($username, $password): bool
    {
        $client = self::getClient();
        try {
            $result = $client->post('/app/pb/wap/login', [
                'json' => [
                    'device' => "Pixel 2",
                    'orgCode' => "gdskills",
                    'organizationId' => "",
                    'password' => md5($password),
                    'registrationId' => $username,
                    'username' => $username,
                    'uuid' => $username,
                ],
            ]);
            $json = json_decode($result->getBody(), true);
            if (!isset($json['result']['userToken'])) {
                return false;
            }
            self::$userToken = urlencode($json['result']['userToken']);
        } catch (GuzzleException $e) {
            return false;
        }
        return true;
    }


    protected static function getClient(): Client
    {
        if (!self::$client instanceof Client) {
            self::$client = new Client([
                'base_uri' => 'http://igw.gdskills.cn',
                'connect_timeout' => 1,
                'timeout' => 3,
            ]);
        }
        return self::$client;
    }

    public static function getPage(): bool
    {
        $client = self::getClient();
        try {
            $result = $client->get('/app/pb/wap/examv2/question/brush-banks/page?page_num=1&page_size=999', [
                'headers' => [
                    'user-token' => self::$userToken,
                ]
            ]);
            $json = json_decode($result->getBody(), true);
            if (count($json['result']['records']) == 0) {
                print_r("该账号不存在题库\n");
                return false;
            }
            foreach ($json['result']['records'] as $key => $record) {
                self::$records[] = $record;
                print_r($key . '：' . $record['bankName'] . "\n");
            }
        } catch (GuzzleException $e) {
            return false;
        }
        return true;
    }

    public static function getQuestions(int $number): ?array
    {
        if (!isset(self::$records[$number])) {
            print_r("序号不存在");
            return null;
        }

        $client = self::getClient();

        $result = $client->post('/app/pb/wap/examv2/question/brush-banks/questions/generation', [
            'json' => [
                'questionBankBrushId' => self::$records[$number]['id'],
                'questionCount' => self::$records[$number]['questionCount'],
                'type' => "1",
            ],
            'headers' => [
                'user-token' => self::$userToken,
            ]
        ]);
        $json = json_decode($result->getBody(), true);
        return $json['result']['questions'] ?? null;
    }

    public static function getBankName(int $number): ?string
    {
        if (!isset(self::$records[$number])) {
            print_r("序号不存在");
            return null;
        }

        return self::$records[$number]['bankName'];
    }
}

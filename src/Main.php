<?php

namespace Onekb\Gdskills;

class Main
{
    public function run()
    {
        print_r("欢迎使用 - 仅为教学示范使用（广东省技工院校职业技能等级认定信息化服务平台-职业技能等级认定）\n\n");
        print_r('请输入账号：');
        $username = trim(fgets(STDIN));
        print_r('请输入密码：');
        $password = trim(fgets(STDIN));
        if (Guzzle::login($username, $password)) {
            print_r("登录成功\n");
        } else {
            die('登录失败');
        }
        print_r("扫描题库中\n");
        if (!Guzzle::getPage()) {
            die('结束');
        }
        print_r("请输入题库序号：");
        $no = (int)trim(fgets(STDIN));
        $questions = Guzzle::getQuestions($no);
        if (!$questions) {
            die('结束');
        }
        $bankName = Guzzle::getBankName($no);

        $name = $this->handleQuestion($bankName, $questions);
        print_r("输出成功：$name\n");
        $name = $this->handleQuestion($bankName, $questions, false);
        print_r("输出成功：$name\n");
    }

    public function handleQuestion(string $bankName, array $data, $answerOpt = true): string
    {
        $bankName = str_replace(['/', '\\', ':', '*', '"', '<', '>', '|', '?'], '_', $bankName);

        $text = '';
        foreach ($data as $item) {
            $type = $item['type'];
            $title = $item['title'];
            $optionData = json_decode($item['optionData'], true);
            $answer = $item['answer'];
            switch ($type) {
                case 1:
                    $text .= '(单选题) ';
                    break;
                case 2:
                    $text .= '(多选题) ';
                    break;
                case 3:
                    $text .= '(判断题) ';
                    break;
            }
            $text .= $title . ($answerOpt ? ' ' . $answer : '') . "\n";
            foreach ($optionData ?? [] as $option) {
                $text .= $option['optionNo'] . '. ' . $option['optionContent'] . "\n";
            }
            $text .= "\n";
        }
        $fileName = $bankName . ($answerOpt ? '（答案）' : '') . '.txt';
        file_put_contents($fileName, $text);
        return $fileName;
    }
}

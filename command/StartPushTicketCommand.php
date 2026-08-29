<?php

namespace app\wechat\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use Throwable;
use app\wechat\libs\utils\RequestUtils;
use app\wechat\service\OpenService;

/**
 * 启动微信开放平台 Ticket 推送命令
 */
class StartPushTicketCommand extends Command
{
    /**
     * 配置命令
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('wechat:start-push-ticket')
            ->setDescription('向微信请求启动/恢复推送 component_verify_ticket');
    }

    /**
     * 执行启动 Ticket 推送请求
     *
     * @param Input $input 输入对象
     * @param Output $output 输出对象
     * @return int 命令退出码
     */
    protected function execute(Input $input, Output $output)
    {
        $output->writeln('<info>正在向微信请求启动推送 component_verify_ticket...</info>');

        try {
            $openService = new OpenService();
            $response = $openService->openAgency()->startPushTicket();
        } catch (Throwable $exception) {
            $output->writeln('<error>请求失败: ' . $exception->getMessage() . '</error>');
            return 1;
        }

        if (RequestUtils::isRquestSuccessed($response)) {
            $output->writeln('<info>请求成功！微信将向授权事件接收 URL 异步推送最新 Ticket。</info>');
            return 0;
        }

        $errorMessage = RequestUtils::buildErrorMsg($response);
        $output->writeln('<error>请求失败: ' . $errorMessage . '</error>');
        return 1;
    }
}

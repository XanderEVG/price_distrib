<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class SecurityListener - Проверка залогинен ли юзер или корректен ли csrf-токен
 * @package App\EventListener
 */
class SecurityListener
{
    private RequestEvent $event;

    /**
     * onKernelController method will be executed on each request
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $this->event = $event;
        $request = $event->getRequest();

        $request_method = $request->server->get('REQUEST_METHOD');
        $url = $request->server->get('REQUEST_URI');
        $urls_parth = explode('?', $url);
        $url = $urls_parth[0] ?? "unknown";

        if (strpos($url, "/api/") === 0) {
            //Проверяем csrf_token для пост обычных запросов
            if ($request_method == "POST" or $request_method == "PUT" or $request_method == "DELETE") {
                $this->csrfChecker($request);
            }
        }

        if (strpos($url, "/devices_api/") === 0) {
            //Проверяем api token
            $this->devicesTokenChecker($request);
        }

    }

    /**
     * csrfChecker() - проверка csrf-токена
     * @param Request $request
     */
    private function csrfChecker(Request $request)
    {
        $session = $request->getSession();
        $csrf_token = $request->headers->get('X-CSRF-TOKEN');
        $session_csrf_token = $session->get($_ENV['CSRF_TOKEN_NAME'] ?? '_csrf/https-main_token');

        if ($csrf_token != $session_csrf_token) {
            $this->interceptAnswer("Неверный CSRF токен", "BAD_CSRF");
        }
    }

    /**
     * devicesTokenChecker() - проверка api-токена
     * @param Request $request
     */
    private function devicesTokenChecker(Request $request)
    {
        $api_token = $request->headers->get('api-token');

        // todo - брать токены из базы
        if ($api_token != 'qweqwe') {
            $this->interceptAnswer("Неверный api токен", "BAD_API_TOKEN");
        }
    }

    /**
     * interceptAnswer() - Перехватывает ответ у контроллера, возвращая ошибку
     * @param string $msg
     * @param null $error_code
     * @param int $response_code
     */
    private function interceptAnswer($msg = "unknown", $error_code = null, $response_code = 403)
    {
        $data = array('success' => false, 'msg' => $msg);
        if ($error_code) {
            $data['critical_error'] = $error_code;
        }
        $response = new JsonResponse($data, $response_code);
        $this->event->setResponse($response);
    }
}

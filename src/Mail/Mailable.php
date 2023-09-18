<?php

namespace Asahasrabuddhe\LaravelMJML\Mail;

use Asahasrabuddhe\LaravelMJML\Process\MJML;
use Illuminate\Mail\Mailable as IlluminateMailable;
use Illuminate\Support\Facades\View;

class Mailable extends IlluminateMailable
{
    /**
     * The MJML template for the message (if applicable).
     *
     * @var string
     */
    protected $mjml;

    /**
     * The MJML content for the message (if applicable).
     *
     * @var string
     */
    protected $mjmlContent = '';

    /**
     * Set the MJML template for the message.
     *
     * @param  string  $view
     * @param  array  $data
     * @return $this
     */
    public function mjml($view, array $data = [])
    {
        $this->mjml     = $view;
        $this->viewData = array_merge($this->buildViewData(), $data);

        return $this;
    }

    /**
     * Set the MJML content for the message.
     *
     * @param  string  $mjmlContent
     * @return $this
     */
    public function mjmlContent($mjmlContent)
    {
        $this->mjmlContent = $mjmlContent;

        return $this;
    }

    /**
     * Build the view for the message.
     *
     * @return array|string
     */
    protected function buildView()
    {
        if (isset($this->mjml) || isset($this->mjmlContent)) {
            return $this->buildMjmlView();
        }
        return parent::buildView();
    }

    /**
     * Build the MJML view for the message.
     *
     * @return array
     */
    protected function buildMjmlView()
    {
        if (isset($this->mjml)) {
            $this->mjmlContent = View::make($this->mjml, $this->buildViewData());
        }
        $mjml = new MJML($this->mjmlContent);

        return [
            'html' => $mjml->renderHTML(),
            'text' => $mjml->renderText(),
        ];
    }
}

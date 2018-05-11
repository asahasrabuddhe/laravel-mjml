<?php

namespace Asahasrabuddhe\LaravelMJML\Mail;

use Illuminate\Support\Facades\View;
use Asahasrabuddhe\LaravelMJML\Process\MJML;
use Illuminate\Mail\Mailable as IlluminateMailable;

class Mailable extends IlluminateMailable
{
    /**
     * The MJML template for the message (if applicable).
     *
     * @var string
     */
    protected $mjml;

    /**
     * Set the Markdown template for the message.
     *
     * @param  string  $view
     * @param  array  $data
     * @return $this
     */
    public function mjml($view, array $data = [])
    {
        $this->mjml     = $view;
        $this->viewData = array_merge($this->viewData, $data);

        return $this;
    }

    /**
     * Build the view for the message.
     *
     * @return array|string
     */
    protected function buildView()
    {
        if (isset($this->mjml)) {
            return $this->buildMjmlView();
        }
        if (isset($this->markdown)) {
            return $this->buildMarkdownView();
        }
        if (isset($this->view, $this->textView)) {
            return [$this->view, $this->textView];
        } elseif (isset($this->textView)) {
            return ['text' => $this->textView];
        }

        return $this->view;
    }

    /**
     * Build the MJML view for the message.
     *
     * @return array
     */
    protected function buildMjmlView()
    {
        $view = View::make($this->mjml, $this->viewData);
        $mjml = new MJML($view);

        return [
            'html' => $mjml->renderHTML(),
            'text' => $mjml->renderText(),
        ];
    }
}

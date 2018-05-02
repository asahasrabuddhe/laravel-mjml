<?php

namespace Asahasrabuddhe\LaravelMJML\Mail;

use Illuminate\Mail\Mailable as IlluminateMailable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\View;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File;

class Mailable extends IlluminateMailable
{
    /**
     * The MJML template for the message (if applicable)
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
        $this->mjml = $view;
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
        $compiledView = storage_path('framework/views/' . sha1($view->getPath()) . '.php');
        $html = $view->render();
        File::put($compiledView, $html);
        return [
            'html' => new HtmlString($this->buildMjmlHtml($compiledView)),
        ];
    }

    protected function buildMjmlHtml($html)
    {
        $process = new Process('node /var/www/html/node_modules/.bin/mjml ' . $html . ' -o ' . $html);
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return File::get($html);
    }

}
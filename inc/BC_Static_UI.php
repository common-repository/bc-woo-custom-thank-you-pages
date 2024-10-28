<?php

/**
 * This class print UI elements that aren't dependent on any particular form (without creating a form instance)
 */

namespace BinaryCarpenter\BC_TK;

class BC_Static_UI
{
    /**
     * Echos an label element
     *
     * @param $field_id
     * @param string $text
     * @return string
     */
    public static function label($field_id, $text, $echo = true)
    {
        $output = sprintf(
            '<label for="%1$s" class="bc-doc-label">%2$s</label>',
            esc_attr($field_id),
            esc_attr($text)
        );
        if ($echo)
            echo $output;
        else
            return $output;
    }

    /**
     * @param array $content array(array('title' => 'title', 'content' => array of string, 'is_active" => false, 'is_disabled' => false))
     * @param bool $echo  or not
     *
     * @return string|void
     */
    public static function tabs($content, $echo = false)
    {
        $tab_head = '';
        $tab_body = '';

        foreach ($content as $item) {
            $active_class = isset($item['is_active']) && $item['is_active'] ? 'bc-uk-active' : '';
            $disabled_class = isset($item['is_disabled']) && $item['is_disabled'] ? 'bc-uk-disabled' : '';
            $tab_head .= sprintf(
                '<li class="%1$s %2$s" ><a href="#">%3$s</a></li>',
                esc_attr($disabled_class),
                esc_attr($active_class),
                esc_attr($item['title'])
            );

            $tab_body .= sprintf('<li>%1$s</li>', implode("", $item['content']));
        }

        $tab_head = sprintf('<ul bc-uk-tab>%1$s</ul>', $tab_head);

        $tab_body = sprintf('<ul class="bc-uk-switcher">%1$s</ul>', $tab_body);

        $html = $tab_head . $tab_body;

        if ($echo)
            echo $html;
        else
            return $html;
    }


    /**
     * @param string $content HTML content of the heading, usually just text
     * @param int $level heading level, similar to h1 to h6 but with smaller text. There are only three levels
     * with text size 38px, 24px and 18px
     *
     * @return string
     *
     */
    public static function heading($content, $level = 1, $echo = true)
    {
        $output = sprintf('<div class="bc-doc-heading-%1$s">%2$s</div>', esc_attr($level), esc_attr($content));

        if ($echo)
            echo $output;
        else
            return $output;
    }


    /**
     * @param string $content html content
     * @param string $type [error|info|warning|success]
     * @param bool $closable
     * @param bool $echo
     * @return string
     */

    public static function link($url, $text)
    {
        return sprintf('<a href="%1$s" target="_blank">%2$s</a>', esc_url($url), esc_attr($text));
    }
    public static function notice($content, $type, $closable = false, $echo = true)
    {
        switch ($type) {
            case 'info':
                $type_class = 'bc-uk-alert-primary';
                break;

            case 'success':
                $type_class = 'bc-uk-alert-success';
                break;

            case 'warning':
                $type_class = 'bc-uk-alert-warning';
                break;

            case 'error':
                $type_class = 'bc-uk-alert-danger';
                break;

            default:
                $type_class = 'bc-uk-alert-primary';
                break;
        }

        $closable = $closable ? '<a class="bc-uk-alert-close" bc-uk-close></a>' : '';

        $output = sprintf(
            '<div class="%1$s" bc-uk-alert> %2$s <p>%3$s</p> </div>',
            esc_attr($type_class),
            esc_attr($closable),
            $content //content must be escaped from the caller
        );

        if ($echo)
            echo $output;
        else
            return $output;
    }

    public static function flex_section($content, $flex_class = 'bc-uk-flex-left')
    {
        $html = sprintf('<div class="bc-uk-flex %1$s">', esc_attr($flex_class));

        foreach ($content as $c)
            $html .= sprintf('<div>%1$s</div>', $c);

        return $html . '</div>';
    }
}

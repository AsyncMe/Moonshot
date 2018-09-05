<?php
/**
 * Created by PhpStorm.
 * User: xiequan
 * Date: 2018/9/5
 * Time: 下午3:12
 */

namespace libs\asyncme;

define('JH_UNKNOW',0);
define('JH_ARRAY',1);
define('JH_STRING_JOSN',2);
define('JH_STRING_HTML',3);
define('JH_STRING_XML',4);

class NgJsonHtml
{
    private $original_data = null;
    private $original_type = JH_UNKNOW;
    private $pendding_data = [];
    private $result = [];

    public function __construct($data=null)
    {
        $this->original_data = $data;
    }

    public function setData($data)
    {
        $this->original_data = $data;
    }

    public function getOriginalData() {
        return $this->original_data;
    }

    public function parse_data()
    {
        if (!$this->original_data) {
            $this->original_type = JH_UNKNOW;
            throw new \Exception('data not null');
        }

        if (is_array($this->original_data)) {
            $this->original_type = JH_ARRAY;
            $this->pendding_data = $this->original_data;

        } else if (is_string($this->original_data)) {
            $prefix_string = substr($this->original_data,0,5);
            if ('<?xml'== $prefix_string) {
                $this->original_type = JH_STRING_XML;

                $json_xml=json_encode($this->original_data);
                $dejson_xml=json_decode($json_xml,true);
                $this->pendding_data = $dejson_xml;

            } else if (in_array(substr($prefix_string,0,1),['[','{'])) {
                $this->original_type = JH_STRING_JOSN;
                $dejson_xml=json_decode($this->original_data,true);
                $this->pendding_data = $dejson_xml;
            } else {
                $this->original_type = JH_STRING_HTML;
                throw new \Exception('data not vaild by html');
            }
        } else {
            throw new \Exception('data not vaild');
        }
    }

    public function parse()
    {
        $this->parse_data();
        $this->handle($this->pendding_data);
        $result_html = implode('',$this->result);
        //unset($this->result);
        return $result_html;
    }
    public function getResultArray()
    {
        return $this->result;
    }

    protected function parse_wrap($wrap)
    {
        $wrap_result = [
            'start'=>'',
            'end'=>'',
        ];
        if ($wrap) {
            $wraps = explode('->',$wrap);
            $tmp = [];
            foreach($wraps as $wrap_tag) {
                $tmp[] = '<'.$wrap_tag.'>';
            }
            $wrap_result['start'] =implode('',$tmp);
            unset($tmp);
            $tmp = [];
            while($wraps) {
                $tmp[] = '</'.array_pop($wraps).'>';
            }
            $wrap_result['end'] =implode('',$tmp);
        }

        return $wrap_result;
    }
    /**
     * 出来选项框
     * @param $type
     * @param $data
     * @param $id
     * @param string $attr_str
     */
    private function hanlde_input_r($type,$data,$id,$attr_str='') {
        $tag_name = 'input';
        $options = $data['options'];
        $seperator = $data['sep'];

        $wrap_title = $data['wrap_title'];
        $wrap_title_result = $this->parse_wrap($wrap_title);

        $wrap = $data['wrap'];
        $wrap_result = $this->parse_wrap($wrap);

        $wrap_option = $data['wrap_option'];
        $wrap_option_result = $this->parse_wrap($wrap_option);

        $title = $data['title'];
        if ($options && is_array($options)) {
            $loop_index=0;

            if ($title) {
                if($wrap_title) {
                    $this->result[] = $wrap_title_result['start'].''.$title.''.$wrap_title_result['end'];
                } else {
                    $this->result[] = '<div>'.$title.'</div>';
                }
            }

            if($wrap_result['start']) $this->result[] =$wrap_result['start'];
            foreach ($options as $opt_key=>$opt_val) {
                $sub_id = $id."_".$loop_index;
                if($wrap_option_result['start']) $this->result[] =$wrap_option_result['start'];
                $checked = $opt_val['checked'] ? 'checked="checked"' : '';
                $this->result[] = '<'.$tag_name.$attr_str.' id="'.$sub_id.'" type="'.$type.'" '.$checked.' value="'.$opt_val['value'].'"/>'.$opt_key."".$seperator ;
                if($wrap_option_result['end']) $this->result[] =$wrap_option_result['end'];
                $loop_index++;
            }
            if($wrap_result['end']) $this->result[] =$wrap_result['end'];
        }
    }

    /**
     * 处理下拉框
     * @param $type
     * @param $data
     * @param $id
     * @param string $attr_str
     */
    private function hanlde_select_option($type,$data,$id,$attr_str='') {
        $tag_name = $type;
        $options = $data['options'];
        $prefix = $data['prefix'];

        $wrap = $data['wrap'];
        $wrap_result = $this->parse_wrap($wrap);

        $wrap_option = $data['wrap_option'];
        $wrap_option_result = $this->parse_wrap($wrap_option);

        if($wrap_result['start']) $this->result[] =$wrap_result['start'];
        $this->result[] = '<'.$tag_name.$attr_str.' id="'.$id.'>';

        if ($options && is_array($options)) {
            $loop_index = 0;
            foreach ($options as $opt_key=>$opt_val) {
                $sub_id = $id."_".($loop_index+1);

                $title = $wrap_option_result['start'].$opt_key.$wrap_option_result['end'];
                $selected = $opt_val['selected'] ? 'selected="selected"' : '';
                $this->result[] = '<option'.' id="'.$sub_id.'" '.$selected.' value="'.$opt_val['value'].'">'.$prefix.$title.'</option>';

            }

        }
        $this->result[] = '</'.$tag_name.'>';
        if($wrap_result['end']) $this->result[] =$wrap_result['end'];
    }

    private function hanlde_upload($type,$data,$id,$attr_str='') {

    }

    /**
     * 处理数据
     * @param $data
     * @param int $parent_id
     */
    private function handle($data,$parent_id=0)
    {
        if($data && is_array($data)) {
            foreach($data as $key=>$val) {
                $type = $val['type'];
                if ($parent_id) {
                    $id = $parent_id.'_'.$key;
                } else {
                    $id = $key+1;
                }

                $attr = $val['attr'];

                $attr_str = '';
                if (is_array($attr) && !empty($attr)) {
                    $attr_tmp = [];
                    if (isset($attr['id'])) {
                        $id = $attr['id'];
                        unset($attr['id']);
                    }
                    foreach ($attr as $attr_key=>$attr_val) {
                        if (is_numeric($attr_key)) {
                            $attr_tmp[] = $attr_val;
                        } else {
                            $attr_tmp[] = $attr_key.'="'.$attr_val.'"';
                        }
                    }
                    if (!empty($attr_tmp)) {
                        $attr_str = ' '.implode(' ',$attr_tmp).' ';
                    }

                    unset($attr_tmp);
                }
                $tag_name = $type;
                if (in_array($type,['radio','checkbox'])) {
                    $this->hanlde_input_r($type,$val,$id,$attr_str);

                } else if (in_array($type,['select'])) {
                    $this->hanlde_select_option($type,$val,$id,$attr_str);
                } else if (in_array($type,['upload'])) {
                    $this->hanlde_upload($type,$val,$id,$attr_str);
                } else {
                    //标签开始
                    $wrap = $val['wrap'];
                    $wrap_result = $this->parse_wrap($wrap);

                    if($wrap_result['start']) $this->result[] = $wrap_result['start'];

                    if (in_array($tag_name, ['br', 'hr', 'input', 'image'])) {
                        $this->result[] = '<' . $tag_name . $attr_str . ' id="' . $id . '"/>';
                    } else {
                        $this->result[] = '<' . $tag_name . $attr_str . ' id="' . $id . '">';
                    }
                    $children = $val['children'];
                    if ($children) {
                        $this->handle($children, $id);
                    }
                    //标签结束
                    if (!in_array($tag_name, ['br', 'hr', 'input', 'image'])) {
                        $this->result[] = '</' . $tag_name . '>';
                    }
                    if($wrap_result['end'])$this->result[] = $wrap_result['end'];

                }
            }
        }
    }

}
?>
<?php

$testjson = [
    [
        'type'=>'form',
        'attr'=>['name'=>'form1','action'=>'#','method'=>'post','enctype'=>'multipart/form-data'],
        'children'=>[
            [
                'type'=>'tr',
                'attr'=>['class'=>'row'],
                'children'=>
                    [
                        [
                            'type'=>'input',
                            'wrap'=>'td->div',
                            'attr'=>['name'=>'title','value'=>'','method'=>'post','placeholder'=>'请输入名称'],
                        ],
                    ],
            ],
            [
                'type'=>'tr',
                'attr'=>['class'=>'row'],
                'children'=>
                    [
                        [
                            'type'=>'radio',
                            'title'=>'性别',
                            'attr'=>['name'=>'sex'],
                            'wrap_title'=>'td->div',
                            'wrap'=>'td->div',
                            'wrap_option'=>'',
                            'options'=>[
                                '保密'=>['value'=>0,],
                                '男士'=>['value'=>1,'checked'=>true],
                                '女士'=>['value'=>2],
                            ]
                        ],
                    ]
            ],
            [
                'type'=>'tr',
                'attr'=>['class'=>'row'],
                'children'=>
                    [
                        [
                            'type'=>'select',
                            'attr'=>['name'=>'grade'],
                            'wrap'=>'td->div',
                            'wrap_option'=>'',
                            'options'=>[
                                '未知'=>['value'=>0,],
                                '小学'=>['value'=>1,'selected'=>true],
                                '初中'=>['value'=>2],
                                '高中'=>['value'=>3],
                                '大学'=>['value'=>4],
                            ]
                        ],
                    ]
            ],


        ]

    ]

];


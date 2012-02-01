<?php
/**
 * @link http://pagination.ru/
 * @author Vasiliy Makogon
 */
class Krugozor_Pagination_Manager
{
    /**
     * ������������ ���������� ������� �� ����,
     * ������� ���������� �������� �� ����� ��������.
     * ���� �� ���������� ������������.
     *
     * @var int
     */
    private $limit;

    /**
     * ����� ������� ��������.
     *
     * @var int
     */
    private $current_page;

    /**
     * ����� �������� ����������.
     *
     * @var int
     */
    private $current_sep;

    /**
     * ��������� �������� ��� SQL-��������� LIMIT.
     *
     * @var int
     */
    private $start_limit;

    /**
     * �������� �������� ��� SQL-��������� LIMIT.
     *
     * @var int
     * @todo
     */
    private $stop_limit;

    /**
     * ����� ���������� ������� � ������� ��, �����������
     * � ����������� � ������������ ������ ��� ����������.
     *
     * @var int
     */
    private $total_rows;

    /**
     * ���������� ������ �� ��������, �����������
     * ����� ��������-������� ���������� << � >>.
     * ����������, ��� ���������� ������ � ��������� �����.
     *
     * @var int
     */
    private $link_count;

    /**
     * ���������� �������, ������� ���������, ���� �� ���� ��������
     * ���������� �������� $this->limit ������� �� ����.
     *
     * @var int
     */
    private $total_pages;

    /**
     * ���������� ������ �������, �� ������� ����� ��������� ��.
     *
     * @var int
     */
    private $total_blocks;

    /**
     * ��� ���������� �� �������, ������� ����� ��������� ��������.
     *
     * @var int
     */
    private $page_var_name;

    /**
     * ��� GET-���������� �� �������, ������� ����� ��������� ���� ������� (���������).
     *
     * @var int
     */
    private $separator_var_name;

    /**
     * @param int $limit - ���������� ������� �� ��������
     * @param int $link_count - ���������� ������ ����� ������� << � >>
     * @param string $page_var_name - ��� ����� ����������, �� ����������� �������������� �������,
     *                                ����������� �������� ��� ��������.
     * @param string $separator_var_name - ��� ����� ���������� �� ����������� �������������� �������,
     *                                     ����������� ���� ������� ��� ��������.
     * @param string $request - ��� ����������� �������������� ������� ("GET", "POST" ��� "REQUEST"),
     *                          �� �������� �������� ���������� � ������� $page_var_name � $separator_var_name.
     * @return void
     */
    public function __construct($limit, $link_count, $page_var_name = 'page', $separator_var_name = 'sep', $request = 'REQUEST')
    {
        $this->limit = intval($limit);
        $this->link_count = intval($link_count);

        $this->page_var_name = $page_var_name;
        $this->separator_var_name = $separator_var_name;

        $request_array_name = '_' . ltrim($request, '_');
        $request = eval("return \$$request_array_name;");

        // ���������� ������� ���������.
        $this->current_sep = isset($request[$separator_var_name]) && is_numeric($request[$separator_var_name])
                            ? intval($request[$separator_var_name])
                            : 1;

        // ���������� ����� ������� ��������
        $this->current_page = isset($request[$page_var_name]) && is_numeric($request[$page_var_name])
                             ? intval($request[$page_var_name])
                             : ($this->current_sep - 1) * $this->link_count + 1;

        $this->start_limit = ($this->current_page - 1) * $this->limit;
        $this->stop_limit  = $this->limit;
    }

    /**
     * ���������� ��������� �������� ��� SQL-��������� LIMIT.
     *
     * @param void
     * @return int
     */
    public function getStartLimit()
    {
        return $this->start_limit;
    }

    /**
     * ���������� �������� �������� ��� SQL-��������� LIMIT.
     *
     * @param void
     * @return int
     */
    public function getStopLimit()
    {
        return $this->stop_limit;
    }

    /**
     * ���������� ����� ���������� �������.
     *
     * @param void
     * @return int
     */
    public function getCount()
    {
        return $this->total_rows;
    }

    /**
     * ��������� �������� �������� - ����� ���������� ������� � ����,
     * � ����� ��������� ��� ����������� ���������� ���
     * ������������ ������ ���������.
     *
     * � ������� ����������� �������� ������� ������, �� ����������� ������� ����� �� �������
     * � ���� �������� ��������� ������� ������. ������ ������, � ��� ��� �� � ��������� ������, ���
     * ��� ��������. �� ��� ��������.
     *
     * @param int
     * @return void
     */
    public function setCount($total_rows)
    {
        $this->total_rows = intval($total_rows);
        $this->total_pages = ceil($this->total_rows/$this->limit);
        $this->total_blocks = ceil($this->total_pages/$this->link_count);

        // ���� ���������� ������ ������ ���� �������, ��
        // �� ���������� ������ ���� ���������� ���� �������.
        $this->total_blocks = ($this->total_blocks > $this->total_pages) ? $this->total_pages : $this->total_blocks;

        // ������� ������� ������ ���� ��� ������ ���������� $this->total_blocks, ��� �� ��� �������� ��������� �������.
        // �.�. ������������, ��� ���� ������, ��������� �� 3 ������, ����� ���������� ������� ������ ���� ����� 6.
        // $this->teoretic_max_count = $this->limit * $this->total_pages;

        // �������� ������ �������� ��� ������ � �������.
        $this->table = array();

        $k = ($this->current_sep - 1) * $this->link_count + 1;

            for ($i = $k; $i < $this->link_count + $k && $i <= $this->total_pages; $i++)
            {
                $temp = ($this->total_rows - (($i-1) * $this->limit));
                $temp2 = ($temp - $this->limit > 0) ? $temp - $this->limit + 1 : 1;

                $temp3 = ($this->limit * ($i - 1)) + 1;
                $temp4 = $i * $this->limit  > $this->total_rows ? $this->total_rows : $i * $this->limit;

                $this->table[] = array
                (
                    'page' => $i,
                    'separator' => $this->current_sep,
                    'decrement_anhor' => ($temp == $temp2 ? $temp : $temp . ' - ' . $temp2),
                    'increment_anhor' => ($temp3 == $temp4 ? $temp3 : $temp3 . ' - ' . $temp4)
                );
            }

        return $this;
    }

    /**
     * ���������� ����� ��� ������ ������� ������� ��� ������������ ���������.
     * � �����, ��� ������ �������, ������ ����� ����� ���������������� ���
     * ������ �������� �����.
     *
     * @param void
     * @return int
     */
    public function getAutodecrementNum()
    {
        return $this->total_rows - $this->start_limit;
    }

    /**
     * ���������� ����� ��� ������ ������� ������� ��� ������������ ���������.
     * � �����, ��� ������ �������, ������ ����� ����� ���������������� ���
     * ������ �������� �����.
     *
     * @param void
     * @return int
     */
    public function getAutoincrementNum()
    {
        return $this->limit * ($this->current_page-1) + 1;
    }

    /**
     * ���������� ����� ���������� ��� ������������ ������ "�� ���������� ���� �������" (<<).
     *
     * @param void
     * @return int
     */
    public function getPreviousBlockSeparator()
    {
        return $this->current_sep - 1 ?: 0;
    }

    /**
     * ���������� ����� ���������� ��� ������������ ������ "�� ��������� ���� �������" (>>).
     *
     * @param void
     * @return int
     */
    public function getNextBlockSeparator()
    {
        return $this->current_sep < $this->total_blocks ? $this->current_sep + 1 : 0;
    }

    /**
     * ���������� ����� ���������� ��� ������������ ������ "�� ��������� ��������" (>>>).
     *
     * @param void
     * @return int
     */
    public function getLastSeparator()
    {
        return $this->total_blocks;
    }

    /**
     * ���������� ����� �������� ��� ������������ ������ "�� ��������� ��������" (>>>).
     *
     * @param void
     * @return int
     */
    public function getLastPage()
    {
        return $this->total_pages;
    }

    /**
     * ���������� ����������� ������ ��� ����� ������ � ������� (��. ������).
     *
     * @param void
     * @return array
     */
    public function getTemplateData()
    {
        return $this->table;
    }

    /**
     * ���������� ����� ������� ��������.
     *
     * @param void
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * ���������� ����� �������� ����������.
     *
     * @param void
     * @return int
     */
    public function getCurrentSeparator()
    {
        return $this->current_sep;
    }

    /**
     * ���������� ����� ���������� ��� ������������ ������ "�� ���������� ��������" (<).
     *
     * @param void
     * @return int
     */
    public function getPreviousPageSeparator()
    {
        // ������� ���������, ����������� ���������
        $cs = ceil($this->current_page / $this->link_count);
        // ���������� ��������� �������� current_page - 1
        $cs2 = ceil(($this->current_page - 1) / $this->link_count);

        // ���� ��������� �������� current_page - 1 ������ �������� ����������,
        // ������ �������� current_page - 1 ��������� � ���������� ����� � ����������� $cs2
        return $cs2 < $cs ? $cs2 : $cs;
    }

    /**
     * ���������� ����� ���������� ��� ������������ ������ "�� ��������� ��������" (>).
     *
     * @param void
     * @return int
     */
    public function getNextPageSeparator()
    {
        // ������� ���������, ����������� ���������.
        $cs = ceil($this->current_page / $this->link_count);
        // ������������������� �������� current_page + 1.
        $cs2 = ceil(($this->current_page + 1) / $this->link_count);

        // ���� ��������� �������� current_page + 1 ������ �������� ����������,
        // ������ �������� current_page + 1 ��������� � ���������� ����� � ����������� $cs2.
        return $cs2 > $cs ? $cs2 : $cs;
    }

    /**
     * ���������� ����� �������� ��� ������������ ������ "�� ���������� ��������" (<).
     *
     * @param void
     * @return int
     */
    public function getPreviousPage()
    {
        return $this->current_page - 1 ?: 0;
    }

    /**
     * ���������� ����� �������� ��� ������������ ������ "�� ��������� ��������" (>).
     *
     * @param void
     * @return int
     */
    public function getNextPage()
    {
        return $this->current_page < $this->total_pages ? $this->current_page + 1 : 0;
    }

    /**
     * ���������� ��� ���������� �� �������, ���������� ����� ����������.
     *
     * @param void
     * @return string
     */
    public function getSeparatorName()
    {
        return $this->separator_var_name;
    }

    /**
     * ���������� ��� ���������� �� �������, ���������� ����� ��������.
     *
     * @param void
     * @return string
     */
    public function getPageName()
    {
        return $this->page_var_name;
    }
}
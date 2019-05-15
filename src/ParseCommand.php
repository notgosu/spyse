<?php
/**
 * Author: Pavel Naumenko
 */

/**
 * Class ParseCommand
 */
class ParseCommand
{
    private $path;
    private $parser;
    private $db;
    private $parentDomainsMap = [];

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->parser = new JsonParser();
        $this->db = new Db();
    }

    public function run()
    {
        $handle = fopen($this->path, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $data = $this->parser->parse($line);

                if (!empty($data)) {
                    $this->db->bulkInsert($this->prepare($data));
                }
            }

            fclose($handle);
        } else {
            echo 'error while opening';
        }
    }

    private function prepare(array $data): array
    {
        $prepared = [];

        if (isset($data['data']['answers'])) {
            foreach ($data['data']['answers'] as $k => $dns) {
                if (isset($dns['type']) && $dns['type'] === 'A') {
                    $prepared[] = [
                        'domain' => $dns['name'],
                        'parent_domain_id' => $this->getParentDomain($dns['name']),
                        'ip' => $dns['answer']
                    ];
                }
            }
        }

        if (isset($data['data']['additionals'])) {
            foreach ($data['data']['additionals'] as $k => $dns) {
                if (isset($dns['type']) && $dns['type'] === 'A') {
                    $prepared[] = [
                        'domain' => $dns['name'],
                        'parent_domain_id' => $this->getParentDomain($dns['name']),
                        'ip' => $dns['answer']
                    ];
                }
            }
        }


        return $prepared;
    }

    private function getParentDomain(string $fullDomain)
    {
        $domain = explode('.', $fullDomain);
        unset($domain[0]);
        $domain = implode('.', $domain);
        echo $domain . "\n";

        //small optimization to limit duplicate select commands
        if (isset($this->parentDomainsMap[$domain])) {
            return $this->parentDomainsMap[$domain];
        }
        $id = $this->db->selectId('domain', $domain);
        if ($id) {
            $this->parentDomainsMap[$domain] = $id;

            return $id;
        }

        //We need to insert this domain, but first let's check does it has subdomain(last-level) before
        if (!empty($domain)) {
            $parentDomain = end(explode('.', $domain));
            $parentDomainId = null;

            if ($parentDomain) {
                $parentDomainId = $this->db->selectId('domain', $parentDomain);
                $parentDomainId = $parentDomainId ?: null;
            }

            return $this->db->insert([
                'domain' => $domain,
                'parent_domain_id' => $parentDomainId
            ]);
        }

        return null;
    }
}

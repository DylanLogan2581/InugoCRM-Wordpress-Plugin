# Security Policy

Digital 55 takes the security of our software products and services seriously, which includes all source code repositories managed through our GitHub organizations and employee accounts. If you believe you have found a security vulnerability in any Digital 55-owned repository, please report it to us as described below.

1. [Reporting security problems to Digital 55](#reporting)
2. [Security Point of Contact](#contact)
3. [Incident Response Process](#process)
4. [Vulnerability Management Plans](#vulnerability-management)

<a name="reporting"></a>
## Reporting security problems to Digital 55

**DO NOT CREATE AN ISSUE** to report a security problem. Instead, please send an email to dlogan@digital-55.com

Please include the requested information listed below (as much as you can provide) to help us better understand the nature and scope of the possible issue:

  * Type of issue (e.g. buffer overflow, SQL injection, cross-site scripting, etc.)
  * Full paths of source file(s) related to the manifestation of the issue
  * The location of the affected source code (tag/branch/commit or direct URL)
  * Any special configuration required to reproduce the issue
  * Step-by-step instructions to reproduce the issue
  * Proof-of-concept or exploit code (if possible)
  * Impact of the issue, including how an attacker might exploit the issue

This information will help us triage your report more quickly.

<a name="contact"></a>
## Security Point of Contact

The security point of contact is [Dylan Logan](mailto:dlogan@digital-55.com). Dylan responds to security incident reports as fast as possible, within one business day at the latest. If Dylan does not respond within two business days, then please contact support@github.com and support@wordpress.org who are able to disable any access for the InugoCRM Wordpress Plugin until the security incident is resolved.

<a name="process"></a>
## Incident Response Process

In case an incident is discovered or reported, Digital 55 will follow the following process to contain, respond and remediate:

### 1. Containment

The first step is to find out the root cause, nature and scope of the incident.

- Is still ongoing? If yes, first priority is to stop it.
- Is the incident outside of our influence? If yes, first priority is to contain it.
- Find out who knows about the incident and who is affected.
- Find out what data was potentially exposed.

One way to immediately remove all access for the InugoCRM Wordpress Plugin is to disable the plugin from the Wordpress Admin Dashboard. The access can be recovered later by re-enabling the InugoCRM Wordpress Plugin.

### 2. Response

After the initial assessment and containment to our best abilities, Digital 55 will document all actions taken in a response plan.

Digital 55 will create a comment in [the official "Updates" issue](https://github.com/DylanLogan2581/InugoCRM-Wordpress-Plugin/issues/1) to inform users about the incident and which actions were taken to contain it.

### 3. Remediation

Once the incident is confirmed to be resolved, Digital 55 will summarize the lessons learned from the incident and create a list of actions Digital 55 will take to prevent further incidents.

<a name="vulnerability-management"></a>
## Vulnerability Management Plans

### Keep permissions to a minimum

The InugoCRM Wordpress Plugin uses the least amount of access to limit the impact of possible security incidents, see [Information collection and use](PRIVACY.md#information-collection-and-use).

If someone would get access to the WIP InugoCRM Wordpress Plugin, the worst thing they could do is to read out contents from pull requests, limited to repositories the WIP got installed on.

### Critical Updates And Security Notices

We learn about critical software updates and security threats from these sources

1. GitHub Security Alerts

## Preferred Languages

We prefer all communications to be in English to allow our team to fully understand your vulnerability report.
